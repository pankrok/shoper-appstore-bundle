<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Maker;

use PanKrok\ShoperAppstoreBundle\Maker\MakeShoperBillingController;
use PanKrok\ShoperAppstoreBundle\Maker\MakeShoperController;
use PanKrok\ShoperAppstoreBundle\Maker\MakeShoperWebhookController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;

/**
 * Command wiring of the three makers. File generation itself is exercised
 * end-to-end against a Symfony app; here we pin names, options and templates.
 */
final class MakersTest extends TestCase
{
    public static function makers(): iterable
    {
        yield 'controller' => [MakeShoperController::class, 'make:shoper-controller', 'ControllerShoper.tpl.php', ['no-template']];
        yield 'billing'    => [MakeShoperBillingController::class, 'make:shoper-billing-controller', 'BillingControllerShoper.tpl.php', []];
        yield 'webhook'    => [MakeShoperWebhookController::class, 'make:shoper-webhook-controller', 'WebhookControllerShoper.tpl.php', ['secret']];
    }

    #[DataProvider('makers')]
    public function testCommandIsConfiguredWithClassArgumentAndOptions(string $class, string $name, string $template, array $options): void
    {
        self::assertSame($name, $class::getCommandName());
        self::assertStringContainsString('Shoper', $class::getCommandDescription());

        $command = new Command($name);
        (new $class())->configureCommand($command, new InputConfiguration());

        $definition = $command->getDefinition();
        self::assertTrue($definition->hasArgument('controller-class'));
        self::assertFalse($definition->getArgument('controller-class')->isRequired());
        foreach ($options as $option) {
            self::assertTrue($definition->hasOption($option), "missing --$option");
        }
        self::assertNotEmpty($command->getHelp(), 'help text is read from the .txt file');

        $dependencies = new DependencyBuilder();
        (new $class())->configureDependencies($dependencies);
        self::assertSame([], $dependencies->getMissingDependencies());
    }

    #[DataProvider('makers')]
    public function testTemplateUsesRouteAttributeAndBundleApi(string $class, string $name, string $template): void
    {
        $tpl = file_get_contents(\dirname(__DIR__, 2) . '/src/Maker/controller/' . $template);

        self::assertStringContainsString('use Symfony\Component\Routing\Attribute\Route;', $tpl);
        self::assertStringNotContainsString('Routing\Annotation\Route', $tpl, 'annotations are deprecated on Symfony 7');
        self::assertStringContainsString('extends AbstractController', $tpl);
    }

    public function testWebhookTemplateRejectsBadSignaturesWith403(): void
    {
        $tpl = file_get_contents(\dirname(__DIR__, 2) . '/src/Maker/controller/WebhookControllerShoper.tpl.php');

        self::assertStringContainsString('InvalidWebhookChecksumException', $tpl);
        self::assertStringContainsString('Response::HTTP_FORBIDDEN', $tpl);
        self::assertStringContainsString('->checksum($request', $tpl);
    }
}
