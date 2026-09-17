<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Command;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Collections\Expr\Comparison;
use PanKrok\ShoperAppstoreBundle\Command\TokenRefreshCommand;
use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use PanKrok\ShoperAppstoreBundle\Entity\AccessTokens;
use PanKrok\ShoperAppstoreBundle\Entity\Shops;
use PanKrok\ShoperAppstoreBundle\Repository\AccessTokensRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class TokenRefreshCommandTest extends TestCase
{
    private ApiController&MockObject $api;
    private AccessTokensRepository&MockObject $repo;
    private ?Criteria $criteria = null;

    protected function setUp(): void
    {
        $this->api  = $this->createMock(ApiController::class);
        $this->repo = $this->createMock(AccessTokensRepository::class);
    }

    private function tester(array $tokens): CommandTester
    {
        $this->repo->method('matching')->willReturnCallback(function (Criteria $c) use ($tokens) {
            $this->criteria = $c;

            // matching() is typed AbstractLazyCollection&Selectable (LazyCriteriaCollection in ORM)
            return new class($tokens) extends AbstractLazyCollection implements Selectable {
                public function __construct(private array $items) {}
                protected function doInitialize(): void { $this->collection = new ArrayCollection($this->items); }
                public function matching(Criteria $criteria): Collection { $this->initialize(); return $this->collection->matching($criteria); }
            };
        });

        return new CommandTester(new TokenRefreshCommand($this->api, $this->repo));
    }

    private static function token(string $shop): AccessTokens
    {
        return (new AccessTokens())->setShop((new Shops())->setShop($shop)->setShopUrl("https://$shop"));
    }

    public function testNothingToRefresh(): void
    {
        $tester = $this->tester([]);
        $this->api->expects(self::never())->method('refreshToken');

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('No tokens need refreshing', $tester->getDisplay());
    }

    public function testCriteriaUsesEntityFieldNameAndOptions(): void
    {
        $tester = $this->tester([]);
        $tester->execute(['--limit' => '5', '--hours-ahead' => '99']);

        $where = $this->criteria->getWhereExpression();
        self::assertInstanceOf(Comparison::class, $where);
        self::assertSame('expires_at', $where->getField(), 'must match AccessTokens::$expires_at, not a camelCase alias');
        self::assertSame(Comparison::LT, $where->getOperator());
        self::assertEqualsWithDelta(time() + 23 * 3600, $where->getValue()->getValue()->getTimestamp(), 5, 'hours-ahead is capped at 23');
        self::assertSame(5, $this->criteria->getMaxResults());
    }

    public function testRefreshesEveryTokenAndReportsSuccess(): void
    {
        $tester = $this->tester([self::token('a'), self::token('b')]);
        $this->api->expects(self::exactly(2))->method('refreshToken');

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('Found 2 token(s)', $tester->getDisplay());
        self::assertStringContainsString('All tokens refreshed', $tester->getDisplay());
    }

    public function testFailuresAreReportedPerShopAndExitCodeIsFailure(): void
    {
        $tester = $this->tester([self::token('good'), self::token('bad')]);
        $this->api->method('refreshToken')->willReturnCallback(function (Shops $shop): void {
            if ('bad' === $shop->getShop()) {
                throw new \RuntimeException('invalid_grant');
            }
        });

        self::assertSame(Command::FAILURE, $tester->execute([]));
        $display = $tester->getDisplay();
        self::assertStringContainsString('bad — invalid_grant', $display);
        self::assertStringContainsString('1 token(s) failed', $display);
        self::assertStringContainsString('good', $display);
    }
}
