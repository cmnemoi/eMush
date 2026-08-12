<?php

declare(strict_types=1);

namespace Mush\tests\functional\MetaGame\Query;

use Mush\Chat\Entity\Message;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\MetaGame\Query\GetGameCountersQuery;
use Mush\MetaGame\Query\GetGameCountersQueryHandler;
use Mush\MetaGame\ViewModel\GameCountersViewModel;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class GetGameCountersQueryCest extends AbstractFunctionalTest
{
    private DaedalusService $daedalusService;
    private GetGameCountersQueryHandler $queryHandler;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->queryHandler = $I->grabService(GetGameCountersQueryHandler::class);
        $this->daedalusService = $I->grabService(DaedalusService::class);
    }

    public function shouldReturnZeroWhenNothingQualifies(FunctionalTester $I): void
    {
        $counters = $this->getCounters();

        $I->assertSame(0, $counters->daedalusesInGame);
        $I->assertSame(0, $counters->mushKilled);
        $I->assertSame(0, $counters->messagesSent);
        $I->assertSame(0, $counters->expeditionsStarted);
    }

    public function shouldCountOneOfEachCounter(FunctionalTester $I): void
    {
        $this->startDaedalus($I);
        $this->killMush($I);
        $this->sendMessage($I);
        $this->startExpedition($I);

        $counters = $this->getCounters();

        $I->assertSame(1, $counters->daedalusesInGame);
        $I->assertSame(1, $counters->mushKilled);
        $I->assertSame(1, $counters->messagesSent);
        $I->assertSame(1, $counters->expeditionsStarted);
    }

    public function shouldCountManyOfEachCounter(FunctionalTester $I): void
    {
        $this->startDaedalus($I);
        $this->startAnotherDaedalus($I);
        $this->killMush($I);
        $this->killMush($I);
        $this->sendMessage($I);
        $this->sendMessage($I);
        $this->startExpedition($I);
        $this->startExpedition($I);

        $counters = $this->getCounters();

        $I->assertSame(2, $counters->daedalusesInGame);
        $I->assertSame(2, $counters->mushKilled);
        $I->assertSame(2, $counters->messagesSent);
        $I->assertSame(2, $counters->expeditionsStarted);
    }

    public function shouldExcludeCheaterGames(FunctionalTester $I): void
    {
        $this->startDaedalus($I);
        $this->killMush($I);
        $this->sendMessage($I);
        $this->startExpedition($I);
        $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->switchIsCheater();
        $I->haveInRepository($this->daedalus->getDaedalusInfo()->getClosedDaedalus());

        $counters = $this->getCounters();

        $I->assertSame(0, $counters->daedalusesInGame);
        $I->assertSame(0, $counters->mushKilled);
        $I->assertSame(0, $counters->messagesSent);
        $I->assertSame(0, $counters->expeditionsStarted);
    }

    public function shouldExcludeNonQualifyingPlayersAndMessages(FunctionalTester $I): void
    {
        $this->closePlayer($I, isMush: false, endCause: EndCauseEnum::ASSASSINATED, finished: true);
        $this->closePlayer($I, isMush: true, endCause: EndCauseEnum::ASSASSINATED, finished: false);
        $this->closePlayer($I, isMush: true, endCause: EndCauseEnum::STILL_LIVING, finished: true);
        $this->sendSystemMessage($I);

        $counters = $this->getCounters();

        $I->assertSame(0, $counters->mushKilled);
        $I->assertSame(0, $counters->messagesSent);
    }

    private function getCounters(): GameCountersViewModel
    {
        return $this->queryHandler->execute(new GetGameCountersQuery());
    }

    private function startDaedalus(FunctionalTester $I): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($this->daedalus->getDaedalusInfo());
    }

    private function startAnotherDaedalus(FunctionalTester $I): void
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $daedalus = $this->daedalusService->createDaedalus($gameConfig, 'second_daedalus', LanguageEnum::FRENCH);
        $daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($daedalus->getDaedalusInfo());
    }

    private function killMush(FunctionalTester $I): void
    {
        $this->closePlayer($I, isMush: true, endCause: EndCauseEnum::ASSASSINATED, finished: true);
    }

    private function closePlayer(FunctionalTester $I, bool $isMush, string $endCause, bool $finished): void
    {
        $closedPlayer = (new ClosedPlayer())
            ->setClosedDaedalus($this->daedalus->getDaedalusInfo()->getClosedDaedalus())
            ->setIsMush($isMush)
            ->setEndCause($endCause);

        if ($finished) {
            $closedPlayer->setFinishedAt(new \DateTime());
        }

        $I->haveInRepository($closedPlayer);
    }

    private function sendMessage(FunctionalTester $I): void
    {
        $message = (new Message())
            ->setChannel($this->publicChannel)
            ->setAuthor($this->player->getPlayerInfo())
            ->setMessage('Hello')
            ->setDay(1)
            ->setCycle(1);
        $I->haveInRepository($message);
    }

    private function sendSystemMessage(FunctionalTester $I): void
    {
        $message = (new Message())
            ->setChannel($this->publicChannel)
            ->setMessage('NERON')
            ->setDay(1)
            ->setCycle(1);
        $I->haveInRepository($message);
    }

    private function startExpedition(FunctionalTester $I): void
    {
        $planet = (new Planet($this->player))->setName(new PlanetName());
        $I->haveInRepository(new Exploration($planet));
    }
}
