<?php

namespace PanKrok\ShoperAppstoreBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class MakeShoperWebhookController extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:shoper-webhook-controller';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new Shoper appstore webhook controller class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf): void
    {
        $command
            ->addArgument(
                'controller-class',
                InputArgument::OPTIONAL,
                sprintf('Choose a name for your controller class (e.g. <fg=yellow>%sController</>)', Str::asClassName(Str::getRandomTerm()))
            )
            ->addOption('secret', null, InputOption::VALUE_OPTIONAL, 'Webhook secret for checksum verification', '')
            ->setHelp(file_get_contents(__DIR__ . '/controller/MakeShoperWebhookController.txt'));

        $inputConf->setArgumentAsNonInteractive('controller-class');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        if (!$input->getArgument('controller-class')) {
            $controllerClass = $io->ask(
                'Enter a class name of your webhook controller',
                null,
                static function (?string $value): string {
                    if (empty($value)) {
                        throw new \RuntimeException('The controller class name cannot be empty.');
                    }
                    return $value;
                }
            );
            $input->setArgument('controller-class', $controllerClass);
        }

        if (!$input->getOption('secret')) {
            $secret = $io->ask('Enter webhook secret (leave empty to configure later)', '');
            $input->setOption('secret', $secret ?? '');
        }
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $controllerClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('controller-class'),
            'Controller\\',
            'Controller'
        );

        $secret = $input->getOption('secret') ?? '';

        $generator->generateController(
            $controllerClassNameDetails->getFullName(),
            __DIR__ . '/controller/WebhookControllerShoper.tpl.php',
            [
                'route_path' => Str::asRoutePath($controllerClassNameDetails->getRelativeNameWithoutSuffix()),
                'route_name' => Str::asRouteName($controllerClassNameDetails->getRelativeNameWithoutSuffix()),
                'secret'     => $secret,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
        $io->text('Next: Open your new controller class and add webhook logic!');
        if (empty($secret)) {
            $io->note('Remember to configure the webhook secret in your controller.');
        }
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }
}
