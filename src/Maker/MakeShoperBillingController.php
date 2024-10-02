<?php

namespace PanKrok\ShoperAppstoreBundle\Maker;

use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\PhpCompatUtil;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class MakeShoperBillingController extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:shoper-billing-controller';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new Shopper appstore billing controller class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf)
    {
        $command
            ->addArgument('controller-class', InputArgument::OPTIONAL, sprintf('Choose a name for your billing controller class (e.g. <fg=yellow>%sController</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__.'/controller/MakeShoperBillingController.txt'))
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $controllerClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('controller-class'),
            'Controller\\',
            'Controller'
        );

        $controllerPath = $generator->generateController(
            $controllerClassNameDetails->getFullName(),
            __DIR__.'/controller/BillingControllerShoper.tpl.php',
            [
                'route_path' => Str::asRoutePath($controllerClassNameDetails->getRelativeNameWithoutSuffix()),
                'route_name' => 'billing_'.Str::asRouteName($controllerClassNameDetails->getRelativeNameWithoutSuffix()),
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
        $io->text('Your billing controller is ready under route: '.Str::asRoutePath($controllerClassNameDetails->getRelativeNameWithoutSuffix()));
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        // $dependencies->addClassDependency(
        //     Annotation::class,
        //     'doctrine/annotations'
        // );
    }
}
