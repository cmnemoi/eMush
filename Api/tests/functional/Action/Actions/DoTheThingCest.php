<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\DoTheThing;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DoTheThingCest
{
    private DoTheThing $doTheThingAction;
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->doTheThingAction = $I->grabService(DoTheThing::class);
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testDoTheThing(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setGameConfig($gameConfig)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);

        $action = new Action();
        $action
            ->setName(ActionEnum::DO_THE_THING)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $femaleCharacterConfig,
        ]);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $maleCharacterConfig,
        ]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('disease')
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setGameConfig($gameConfig)
            ->setName('sex')
            ->setDiseases(['disease'])
        ;
        $I->haveInRepository($diseaseCauseConfig);

        $gameEquipment = new Equipment();
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setConfig($equipmentConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($gameEquipment);

        $didTheThingStatus = new ChargeStatusConfig();
        $didTheThingStatus
            ->setName(PlayerStatusEnum::DID_THE_THING)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;

        $I->haveInRepository($didTheThingStatus);

        $pregnantStatus = new StatusConfig();
        $pregnantStatus
            ->setName(PlayerStatusEnum::PREGNANT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;

        $I->haveInRepository($pregnantStatus);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertNull($this->doTheThingAction->cannotExecuteReason());

        $this->doTheThingAction->execute();

        $I->assertEquals(9, $player->getActionPoint());
        $I->assertEquals(8, $player->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => ActionLogEnum::DO_THE_THING_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // Check if pregnancy log works
        $pregnantStatusEvent = new StatusEvent(
            PlayerStatusEnum::PREGNANT,
            $player,
            $this->doTheThingAction->getActionName(),
            new \DateTime()
        );
        $pregnantStatusEvent->setVisibility(VisibilityEnum::PRIVATE);

        $this->eventDispatcher->dispatch($pregnantStatusEvent, StatusEvent::STATUS_APPLIED);

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'log' => StatusEventLogEnum::BECOME_PREGNANT,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testNoFlirt(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::DO_THE_THING)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $femaleCharacterConfig,
        ]);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $maleCharacterConfig,
        ]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $gameEquipment = new Equipment();
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setConfig($equipmentConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($gameEquipment);

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertEquals(ActionImpossibleCauseEnum::DO_THE_THING_NOT_INTERESTED,
            $this->doTheThingAction->cannotExecuteReason()
        );
    }

    public function testWitness(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::DO_THE_THING)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $femaleCharacterConfig,
        ]);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $maleCharacterConfig,
        ]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $gameEquipment = new Equipment();
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setConfig($equipmentConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($gameEquipment);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        /** @var Player $targetPlayer */
        $witness = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $femaleCharacterConfig,
        ]);

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertEquals(ActionImpossibleCauseEnum::DO_THE_THING_WITNESS,
            $this->doTheThingAction->cannotExecuteReason()
        );
    }

    public function testRoomHasBed(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::DO_THE_THING)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $femaleCharacterConfig,
        ]);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
            'characterConfig' => $maleCharacterConfig,
        ]);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertFalse($this->doTheThingAction->isVisible());
    }
}
