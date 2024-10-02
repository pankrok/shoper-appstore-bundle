<?php

namespace PanKrok\ShoperAppstoreBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use PanKrok\ShoperAppstoreBundle\Repository\AccessTokensRepository;
use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'ShoperAppstoreBundle:TokenRefresh',
    description: 'This command automatickly refresh tokens',
)]
class TokenRefreshCommand extends Command
{

    protected ApiController $api;
    protected AccessTokensRepository $accessTokensRepository;
    private $options;


    public function __construct(ParameterBagInterface $container, ApiController $api, AccessTokensRepository $accessTokensRepository)
    {
        parent::__construct();
        $this->api = $api;
        $this->accessTokensRepository = $accessTokensRepository;
        $this->options =$container->get('appstore');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->write(json_encode($this->options));

        $date = new \DateTimeImmutable('now - 2days');

        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria->where($criteria->expr()->gt('expires_at', $date))
            ->setMaxResults(100)
        ;

        $tokens = $this->accessTokensRepository->matching($criteria);

        if (count($tokens) > 0) {
            $output->write('tokens found: ' . count($tokens) . PHP_EOL);
            foreach ($tokens as $token) {
                $shop = $token->getShop();
                $output->write('shop found: ' . $shop->getShop() . PHP_EOL);
                $params = ['shop' => $shop->getShop()];
                $this->api->setParams($params, false);
                sleep(1);
            }
        } else {
            $output->write('no tokens found... ');
        }


        $io->success('Tokens updated');

        return Command::SUCCESS;
    }
}
