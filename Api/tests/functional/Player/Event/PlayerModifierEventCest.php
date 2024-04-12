<?php

namespace Mush\Tests\functional\Player\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class PlayerModifierEventCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testDispatchMoralChange(FunctionalTester $I)
    {
        $suicidalStatusConfig = new StatusConfig();
        $suicidalStatusConfig
            ->setStatusName(PlayerStatusEnum::SUICIDAL)
            ->buildName(GameConfigEnum::TEST);
        $demoralizedStatusConfig = new StatusConfig();
        $demoralizedStatusConfig
            ->setStatusName(PlayerStatusEnum::DEMORALIZED)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($suicidalStatusConfig);
        $I->haveInRepository($demoralizedStatusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$demoralizedStatusConfig, $suicidalStatusConfig]),
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player->setMoralPoint(5);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        // remove moral bellow demoralized threshold
        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            -1,
            [EventEnum::PLAYER_DEATH],
            new \DateTime()
        );

        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(4, $player->getMoralPoint());
        $I->assertCount(0, $player->getStatuses());

        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(3, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->seeInRepository(
            Status::class,
            [
                'statusConfig' => $demoralizedStatusConfig->getId(),
            ]
        );

        // remove moral bellow suicidal threshold
        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            -2,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(1, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->dontSeeInRepository(
            Status::class,
            [
                'statusConfig' => $demoralizedStatusConfig->getId(),
            ]
        );
        $I->seeInRepository(
            Status::class,
            [
                'statusConfig' => $suicidalStatusConfig->getId(),
            ]
        );

        // remove moral within suicidal threshold
        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            -1,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(0, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->dontSeeInRepository(
            Status::class,
            [
                'statusConfig' => $demoralizedStatusConfig->getId(),
            ]
        );
        $I->seeInRepository(
            Status::class,
            [
                'statusConfig' => $suicidalStatusConfig->getId(),
            ]
        );

        // add moral to remove any status
        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            7,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(7, $player->getMoralPoint());
        $I->assertCount(0, $player->getStatuses());
    }

    public function testDispatchSatietyChange(FunctionalTester $I)
    {
        $fullStatusConfig = new StatusConfig();
        $fullStatusConfig
            ->setStatusName(PlayerStatusEnum::FULL_STOMACH)
            ->buildName(GameConfigEnum::TEST);

        $starvingWarningStatusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => PlayerStatusEnum::STARVING_WARNING]);

        $starvingStatusConfig = new StatusConfig();
        $starvingStatusConfig
            ->setStatusName(PlayerStatusEnum::STARVING)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($fullStatusConfig);
        $I->haveInRepository($starvingStatusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$starvingStatusConfig, $fullStatusConfig, $starvingWarningStatusConfig]),
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player->setSatiety(0);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            -1,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(-1, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            2,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(2, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            1,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(3, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            -1,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(2, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            -27,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(-25, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());
    }

    public function testDispatchMushSatietyChange(FunctionalTester $I)
    {
        $fullStatusConfig = new StatusConfig();
        $fullStatusConfig
            ->setStatusName(PlayerStatusEnum::FULL_STOMACH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($fullStatusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$fullStatusConfig])]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player->setSatiety(0);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($mushConfig);
        $mushStatus = new ChargeStatus($player, $mushConfig);
        $I->haveInRepository($mushStatus);

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            -1,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(-1, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            4,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(4, $player->getSatiety());
        $I->assertCount(2, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            -29,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        $I->assertEquals(-25, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());
    }
}
