<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\DependencyInjection;

use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use PanKrok\ShoperAppstoreBundle\Controller\WebhookController;
use PanKrok\ShoperAppstoreBundle\DependencyInjection\Configuration;
use PanKrok\ShoperAppstoreBundle\DependencyInjection\ShoperAppstoreExtension;
use PanKrok\ShoperAppstoreBundle\EventSubscriber\InstallSubscriber;
use PanKrok\ShoperAppstoreBundle\Events\InstallEvent;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use PanKrok\ShoperAppstoreBundle\Maker\MakeShoperController;
use PanKrok\ShoperAppstoreBundle\Model\Resource\Product;
use PanKrok\ShoperAppstoreBundle\ShoperAppstoreBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class BundleWiringTest extends TestCase
{
    // --- Configuration ---------------------------------------------------------

    private function process(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [$config]);
    }

    public function testConfigurationDefaults(): void
    {
        $config = $this->process(['appId' => 'a', 'appSecret' => 's', 'appstoreSecret' => 'x']);

        self::assertSame('a', $config['appId']);
        self::assertFalse($config['debug']);
        self::assertStringStartsWith('https://dcsaascdn.net/js/dc-sdk-', $config['jssdk']);
        self::assertSame(['maxRetries' => 3, 'throttle' => true], $config['rateLimit']);
    }

    public function testConfigurationAcceptsRateLimitOverrides(): void
    {
        $config = $this->process(['rateLimit' => ['maxRetries' => 0, 'throttle' => false]]);

        self::assertSame(['maxRetries' => 0, 'throttle' => false], $config['rateLimit']);
    }

    public function testConfigurationRejectsNegativeRetries(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process(['rateLimit' => ['maxRetries' => -1]]);
    }

    public function testConfigurationRejectsUnknownKeys(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process(['nope' => 1]);
    }

    // --- Extension -------------------------------------------------------------

    public function testExtensionAliasMatchesDocumentedConfigKey(): void
    {
        self::assertSame('shoper_appstore', (new ShoperAppstoreExtension())->getAlias());
    }

    public function testLoadRegistersServicesAndExposesConfigParameter(): void
    {
        $container = new ContainerBuilder();
        (new ShoperAppstoreExtension())->load([['appId' => 'a', 'appSecret' => 's', 'appstoreSecret' => 'x']], $container);

        self::assertTrue($container->hasDefinition(ApiController::class));
        self::assertTrue($container->hasDefinition(WebhookController::class));
        self::assertTrue($container->hasDefinition(InstallSubscriber::class));
        self::assertTrue($container->getDefinition(ApiController::class)->isAutowired());

        foreach ([Product::class, InstallEvent::class, ShoperApiException::class, Configuration::class] as $notAService) {
            self::assertFalse($container->hasDefinition($notAService), $notAService . ' must not be a service');
        }

        $param = $container->getParameter('appstore');
        self::assertSame('a', $param['appId']);
        self::assertSame(3, $param['rateLimit']['maxRetries']);
    }

    public function testMakerServicesAreRegisteredOnlyWithMakerBundle(): void
    {
        $container = new ContainerBuilder();
        (new ShoperAppstoreExtension())->load([[]], $container);

        // maker-bundle is a dev dependency of this repo, so it is present here
        self::assertTrue($container->hasDefinition(MakeShoperController::class));
        self::assertTrue($container->getDefinition(MakeShoperController::class)->hasTag('maker.command'));
    }

    public function testPrependRegistersAppstoreTwigNamespace(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new TwigExtension());

        (new ShoperAppstoreExtension())->prepend($container);

        $twig  = $container->getExtensionConfig('twig');
        $paths = $twig[0]['paths'] ?? [];
        self::assertSame(['Appstore'], array_values($paths));
        self::assertStringEndsWith('Resources' . DIRECTORY_SEPARATOR . 'views', str_replace('/', DIRECTORY_SEPARATOR, array_key_first($paths)));
        self::assertFileExists(array_key_first($paths) . '/error.html.twig');
    }

    public function testPrependIsNoopWithoutTwig(): void
    {
        $container = new ContainerBuilder();
        (new ShoperAppstoreExtension())->prepend($container);

        self::assertSame([], $container->getExtensionConfig('twig'));
    }

    // --- Bundle ----------------------------------------------------------------

    public function testBundlePathIsRepositoryRoot(): void
    {
        $bundle = new ShoperAppstoreBundle();

        self::assertFileExists($bundle->getPath() . '/composer.json');
        self::assertInstanceOf(ShoperAppstoreExtension::class, $bundle->getContainerExtension());
    }
}
