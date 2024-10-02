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

final class MakeShoperWebhookController extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:shoper-webhook-controller';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new Shopper appstore webhook controller class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf)
    {
        $command
            ->addArgument('controller-class', InputArgument::OPTIONAL, sprintf('Choose a name for your controller class (e.g. <fg=yellow>%sController</>)', Str::asClassName(Str::getRandomTerm())))
            ->addOption('secret', null, InputOption::VALUE_NONE, 'Set secret of your webhook')
            ->setHelp(file_get_contents(__DIR__.'/controller/MakeShoperWebhookController.txt'))
        ;

        $inputConf->setArgumentAsNonInteractive('controller-class');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        if (!$input->getArgument('controller-class')) {
            $controllerClass = $io->ask('Enter a class name of your webhook controller', 'string', [Validator::class, 'notBlank']);
            $input->setArgument('controller-class', $controllerClass);
        }

        if (!$input->getOption('secret')) {
            $secret = $io->ask('Enter a secret of your webhook', 'string', [Validator::class, 'notBlank']);
            $input->setOption('secret', $secret);
        }
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $controllerClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('controller-class'),
            'Controller\\',
            'Controller'
        );

        $secret = $input->getOption('secret');

        $controllerPath = $generator->generateController(
            $controllerClassNameDetails->getFullName(),
            __DIR__.'/controller/WebhookControllerShoper.tpl.php',
            [
                'route_path' => Str::asRoutePath($controllerClassNameDetails->getRelativeNameWithoutSuffix()),
                'route_name' => Str::asRouteName($controllerClassNameDetails->getRelativeNameWithoutSuffix()),
                'secret' => $secret,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
        $io->text('Next: Open your new controller class and add some logic!');
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {

    }

    protected function question(Command $command)
    {
        $question = new Question('Please enter the secret of the Shoper webhook', '');
        $helper = $command->getHelper('question');
        $secret = $helper->ask($input, $output, $question);
    }
}
