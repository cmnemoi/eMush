<?php

namespace Mush\Tests\functional\Disease\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Listener\PlayerCycleSubscriber;
use Mush\Game\Enum\EventEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;

class PlayerCycleSubscriberCest
{
    private PlayerCycleSubscriber $subscriber;

    public function _before(FunctionalTester $I)
    {
        $this->subscriber = $I->grabService(PlayerCycleSubscriber::class);
    }

    public function testOnPlayerCycle(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);

        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);
        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $place,
        ]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('Name')
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(10)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $event = new PlayerCycleEvent(
            $player,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );

        $this->subscriber->onPlayerNewCycle($event);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
            'diseasePoint' => 9,
        ]);
    }

    public function testOnPlayerCycleSpontaneousCure(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);

        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);
        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $place,
        ]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('Name')
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(1)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $event = new PlayerCycleEvent($player, EventEnum::NEW_CYCLE, new \DateTime());

        $this->subscriber->onPlayerNewCycle($event);

        $I->dontSeeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
        ]);

        $I->seeInRepository(RoomLog::class, [
            'player' => $player->getId(),
            'place' => $place->getId(),
            'log' => LogEnum::DISEASE_CURED,
        ]);
    }

    public function testOnPlayerCycleDiseaseAppear(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);

        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);
        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $place,
        ]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('Name')
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::INCUBATING)
            ->setDiseasePoint(1)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $event = new PlayerCycleEvent($player, EventEnum::NEW_CYCLE, new \DateTime());

        $this->subscriber->onPlayerNewCycle($event);

        $I->assertGreaterThan(0, $playerDisease->getDiseasePoint());

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
            'status' => DiseaseStatusEnum::ACTIVE,
        ]);

        $I->seeInRepository(RoomLog::class, [
            'player' => $player,
            'place' => $place,
            'log' => LogEnum::DISEASE_APPEAR,
        ]);
    }

    public function testOnPlayerCycleBitingSymptom(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);

        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);
        $characterConfig = $I->have(CharacterConfig::class);
        $otherCharacterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $place,
        ]);

        $otherPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $place,
        ]);

        $symptomConfig = new SymptomConfig('biting');
        $symptomConfig
            ->setTrigger(EventEnum::NEW_CYCLE)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $event = new PlayerCycleEvent($player, EventEnum::NEW_CYCLE, new \DateTime());

        $this->subscriber->onPlayerNewCycle($event);

        $I->seeInRepository(RoomLog::class, [
            'player' => $player,
            'place' => $place,
            'log' => 'biting',
        ]);
    }

    public function testOnPlayerCyclePsychoticAttackSymptom(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);

        $actionCost = new ActionCost();

        $action = new Action();
        $action
            ->setName(ActionEnum::HIT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setSuccessRate(100)
            ->setActionCost($actionCost);

        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);
        $characterConfig = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$action])]);
        $otherCharacterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $place,
        ]);

        $otherPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $place,
        ]);

        $symptomConfig = new SymptomConfig('psychotic_attacks');
        $symptomConfig
            ->setTrigger(EventEnum::NEW_CYCLE)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $event = new PlayerCycleEvent($player, EventEnum::NEW_CYCLE, new \DateTime());

        $this->subscriber->onPlayerNewCycle($event);

        $I->seeInRepository(RoomLog::class, [
            'player' => $player,
            'place' => $place,
            'log' => 'hit_success',
        ]);
    }

    // @TODO Dirtiness symptom test
}
