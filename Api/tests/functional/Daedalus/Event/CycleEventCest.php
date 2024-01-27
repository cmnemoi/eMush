<?php

namespace Mush\Tests\functional\Daedalus\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class CycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testOxygenCycleSubscriber(FunctionalTester $I)
    {
        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::TRAUMA)
            ->setDiseases([])
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseCause);

        $fireStatusConfig = new ChargeStatusConfig();
        $fireStatusConfig->setStatusName(StatusEnum::FIRE)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($fireStatusConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        $hunterConfigs = $I->grabEntitiesFromRepository(HunterConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCause]),
            'statusConfigs' => new ArrayCollection([$fireStatusConfig]),
            'hunterConfigs' => new ArrayCollection($hunterConfigs),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalus->setOxygen(1);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        $space = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::SPACE]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);
        $characterConfig
            ->setInitHealthPoint(99)
            ->setMaxHealthPoint(99)
        ;
        $I->haveInRepository($characterConfig);
        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::CHUN]);
        $characterConfig2
            ->setInitHealthPoint(99)
            ->setMaxHealthPoint(99)
        ;
        $I->haveInRepository($characterConfig2);

        /** @var Player $player */
        $player = $I->have(
            Player::class, [
                'daedalus' => $daedalus,
                'place' => $room,
            ]
        );
        $player->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(
            Player::class, [
                'daedalus' => $daedalus,
                'place' => $room,
            ]
        );
        $player2->setPlayerVariables($characterConfig);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig2);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        $event = new DaedalusCycleEvent(
            $daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(0, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
    }

    public function testOxygenBreakOnCycleChange(FunctionalTester $I)
    {
        // let's increase the duration of the ship to increase the number of incidents
        $this->daedalus
            ->setOxygen(10)
            ->setDay(100)
        ;

        $this->player->getVariableByName(PlayerVariableEnum::MORAL_POINT)->setMaxValue(200)->setValue(200);
        $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->setMaxValue(200)->setValue(200);

        $tankConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::OXYGEN_TANK]);

        $tankEquipment = $tankConfig->createGameEquipment($this->player->getPlace());
        $I->haveInRepository($tankEquipment);

        $event = new EquipmentEvent(
            $tankEquipment,
            true,
            VisibilityEnum::PUBLIC,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertCount(1, $this->daedalus->getModifiers());

        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // we cannot be sure that the tank is broken, but chances are really high so overall the test works
        // base oxygen loss is -3 with one operational tank it should be -2
        $I->assertEquals(8, $this->daedalus->getOxygen());
    }

    public function testCycleSubscriberDoNotAssignTitleToDeadPlayer(FunctionalTester $I): void
    {
        // given daedalus is in game so titles can be assigned
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);

        // given those players in the daedalus
        /** @var Player $jinSu */
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);
        /** @var Player $gioele */
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);

        // given Jin Su has 0 morale points so he dies at cycle change
        $jinSu->setMoralPoint(0);

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Jin Su is dead and is not commander, but Gioele is commander
        $I->assertFalse($jinSu->isAlive());
        $I->assertEmpty($jinSu->getTitles());
        $I->assertEquals($gioele->getTitles(), [TitleEnum::COMMANDER]);
    }
}
