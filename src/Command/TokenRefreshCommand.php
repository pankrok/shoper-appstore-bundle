<?php

namespace PanKrok\ShoperAppstoreBundle\Command;

use Doctrine\Common\Collections\Criteria;
use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use PanKrok\ShoperAppstoreBundle\Repository\AccessTokensRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shoper:token:refresh',
    description: 'Refresh OAuth tokens that are close to expiry.',
    aliases: ['ShoperAppstoreBundle:TokenRefresh'],
)]
class TokenRefreshCommand extends Command
{
    public function __construct(
        private readonly ApiController $api,
        private readonly AccessTokensRepository $accessTokensRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of tokens to refresh per run.', 200)
            ->addOption('hours-ahead', null, InputOption::VALUE_REQUIRED, 'Refresh tokens expiring within this many hours (max 23).', 23);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $limit      = max(1, (int) $input->getOption('limit'));
        $hoursAhead = min(23, max(1, (int) $input->getOption('hours-ahead')));

        $threshold = new \DateTimeImmutable('@' . (time() + $hoursAhead * 3600));

        $criteria = Criteria::create()
            ->where(Criteria::expr()->lt('expiresAt', $threshold))
            ->setMaxResults($limit);

        $tokens = $this->accessTokensRepository->matching($criteria);

        if (count($tokens) === 0) {
            $io->success('No tokens need refreshing.');
            return Command::SUCCESS;
        }

        $io->note(sprintf('Found %d token(s) to refresh (threshold: %s).', count($tokens), $threshold->format('Y-m-d H:i:s')));

        $failed = 0;
        foreach ($tokens as $token) {
            $shop = $token->getShop();
            try {
                $this->api->refreshToken($shop);
                $io->writeln(sprintf(' <info>✓</info> %s', $shop->getShop()));
            } catch (\Throwable $e) {
                $io->writeln(sprintf(' <error>✗</error> %s — %s', $shop->getShop(), $e->getMessage()));
                ++$failed;
            }
        }

        if ($failed > 0) {
            $io->warning(sprintf('%d token(s) failed to refresh.', $failed));
            return Command::FAILURE;
        }

        $io->success('All tokens refreshed successfully.');
        return Command::SUCCESS;
    }
}
