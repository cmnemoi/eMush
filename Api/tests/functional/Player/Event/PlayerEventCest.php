<?php

namespace Mush\Tests\functional\Player\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;

/**
 * @internal
 */
final class PlayerEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testDispatchPlayerDeath(FunctionalTester $I)
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::DEMORALIZED,
            $this->player,
            ['test'],
            new \DateTime()
        );

        $this->daedalus->setDay(89);
        $this->daedalus->setCycle(5);
        $this->daedalus->setFilledAt(new \DateTime());

        $I->seeInRepository(Status::class);

        $this->playerService->killPlayer(
            player: $this->player,
            endReason: EndCauseEnum::CLUMSINESS,
            time: new \DateTime(),
        );

        $playerInfo = $this->player->getPlayerInfo();
        $daedalusInfo = $this->daedalus->getDaedalusInfo();

        $I->assertEquals(GameStatusEnum::FINISHED, $playerInfo->getGameStatus());
        $closedPlayer = $playerInfo->getClosedPlayer();

        $I->assertEquals($closedPlayer->getEndCause(), EndCauseEnum::CLUMSINESS);
        $I->assertEquals($closedPlayer->getMessage(), null);
        $I->assertEquals($closedPlayer->getCycleDeath(), 5);
        $I->assertEquals($closedPlayer->getDayDeath(), 89);
        $I->assertEquals($closedPlayer->getClosedDaedalus(), $daedalusInfo->getClosedDaedalus());
        $I->assertFalse($closedPlayer->isMush());

        $I->dontSeeInRepository(Status::class);
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $playerInfo->getId(),
            'log' => LogEnum::DEATH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testDispatchPlayerKill(FunctionalTester $I)
    {
        $this->daedalus->setDay(89);
        $this->daedalus->setCycle(5);
        $this->daedalus->setFilledAt(new \DateTime());

        $this->playerService->killPlayer(
            player: $this->player,
            endReason: EndCauseEnum::ASSASSINATED,
            time: new \DateTime(),
            author: $this->player2
        );

        $playerInfo = $this->player->getPlayerInfo();
        $daedalusInfo = $this->daedalus->getDaedalusInfo();

        $I->assertEquals(GameStatusEnum::FINISHED, $playerInfo->getGameStatus());
        $closedPlayer = $playerInfo->getClosedPlayer();
        $closedKiller = $this->player2->getPlayerInfo()->getClosedPlayer();

        $I->assertEquals($closedPlayer->getEndCause(), EndCauseEnum::ASSASSINATED);
        $I->assertEquals($closedPlayer->getMessage(), null);
        $I->assertEquals($closedPlayer->getCycleDeath(), 5);
        $I->assertEquals($closedPlayer->getDayDeath(), 89);
        $I->assertEquals($closedPlayer->getClosedDaedalus(), $daedalusInfo->getClosedDaedalus());
        $I->assertFalse($closedPlayer->isMush());

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $playerInfo->getId(),
            'log' => LogEnum::DEATH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertEquals(
            actual: [
                'name' => 'death.player',
                'result' => PlayerHighlight::SUCCESS,
                'parameters' => ['target_' . $this->player->getLogKey() => $this->player->getLogName()],
            ],
            expected: $closedKiller->getPlayerHighlights()[0]->toArray()
        );
    }

    public function testDispatchPlayerDeathMush(FunctionalTester $I)
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::MUSH,
            $this->player,
            ['test'],
            new \DateTime()
        );

        $this->daedalus->setDay(89);
        $this->daedalus->setCycle(5);
        $this->daedalus->setFilledAt(new \DateTime());

        $this->playerService->killPlayer(
            player: $this->player,
            endReason: EndCauseEnum::CLUMSINESS,
            time: new \DateTime(),
        );

        $playerInfo = $this->player->getPlayerInfo();
        $daedalusInfo = $this->player->getDaedalusInfo();

        $I->assertEquals(GameStatusEnum::FINISHED, $playerInfo->getGameStatus());
        $closedPlayer = $playerInfo->getClosedPlayer();

        $I->assertEquals($closedPlayer->getEndCause(), EndCauseEnum::CLUMSINESS);
        $I->assertEquals($closedPlayer->getMessage(), null);
        $I->assertEquals($closedPlayer->getCycleDeath(), 5);
        $I->assertEquals($closedPlayer->getDayDeath(), 89);
        $I->assertEquals($closedPlayer->getClosedDaedalus(), $daedalusInfo->getClosedDaedalus());
        $I->assertTrue($closedPlayer->isMush());

        $I->dontSeeInRepository(Status::class);
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $playerInfo->getId(),
            'log' => LogEnum::DEATH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testDispatchInfection(FunctionalTester $I)
    {
        $mushStatusConfig = new ChargeStatusConfig();
        $mushStatusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($mushStatusConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FUNGIC_INFECTION)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::INFECTION)
            ->setDiseases([
                DiseaseEnum::FUNGIC_INFECTION => 1,
            ])
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseCause);

        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::INFECTION)
            ->setDiseases([
                DiseaseEnum::FUNGIC_INFECTION => 1,
            ])
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseCause);

        $mushStartConfig = TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::MUSH_INITIAL_BONUS));
        $I->haveInRepository($mushStartConfig);

        $playerInfo = $this->player->getPlayerInfo();
        $daedalusInfo = $this->daedalus->getDaedalusInfo();
        $room = $this->player->getPlace();

        $playerEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::SPORE,
            1,
            [ActionEnum::INFECT->value],
            new \DateTime()
        );

        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);

        $I->assertCount(0, $this->player->getStatuses());
        $I->assertEquals(1, $this->player->getSpores());
        $I->assertEquals($room, $this->player->getPlace());

        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);

        $I->assertCount(0, $this->player->getStatuses());
        $I->assertEquals(2, $this->player->getSpores());
        $I->assertEquals($room, $this->player->getPlace());

        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);

        $I->assertCount(1, $this->player->getStatuses());
        $I->assertEquals(0, $this->player->getSpores());
        $I->assertEquals($room, $this->player->getPlace());
    }

    public function testDispatchConversion(FunctionalTester $I)
    {
        $room = $this->player->getPlace();

        $this->player->setMoralPoint(8)->setSpores(3);

        $playerEvent = new PlayerEvent($this->player, [ActionEnum::INFECT->value], new \DateTime());

        $this->eventService->callEvent($playerEvent, PlayerEvent::CONVERSION_PLAYER);

        $sporesVariable = $this->player->getVariableByName(PlayerVariableEnum::SPORE);

        $I->assertCount(1, $this->player->getStatuses());
        $I->assertEquals(0, $sporesVariable->getValue());
        $I->assertEquals(2, $sporesVariable->getMaxValue());
        $I->assertEquals($room, $this->player->getPlace());
        $I->assertEquals(14, $this->player->getMoralPoint());
        $I->assertEquals(1, $this->daedalus->getDaedalusInfo()->getDaedalusStatistics()->getMushAmount());
    }
}
