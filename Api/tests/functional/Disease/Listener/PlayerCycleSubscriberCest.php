<?php

namespace Mush\Tests\functional\Disease\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Disease\Listener\PlayerCycleSubscriber;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class PlayerCycleSubscriberCest
{
    private PlayerCycleSubscriber $subscriber;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        $this->subscriber = $I->grabService(PlayerCycleSubscriber::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testOnPlayerCycle(FunctionalTester $I): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $place */
        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $place,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(10);

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $event = new PlayerCycleEvent(
            $player,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );

        $this->subscriber->onPlayerNewCycle($event);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
            'diseasePoint' => 9,
        ]);
    }

    public function testOnPlayerCycleSpontaneousCure(FunctionalTester $I): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $place */
        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $place,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(1);

        $I->haveInRepository($playerDisease);
        $I->refreshEntities($player);

        $event = new PlayerCycleEvent($player, [EventEnum::NEW_CYCLE], new \DateTime());

        $this->subscriber->onPlayerNewCycle($event);
        $I->dontSeeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
        ]);

        $I->seeInRepository(RoomLog::class, [
            'playerInfo' => $playerInfo->getId(),
            'place' => $place->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => LogEnum::DISEASE_CURED,
        ]);
    }

    public function testOnPlayerCycleDiseaseAppear(FunctionalTester $I): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $place */
        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $place,
        ]);
        $player->setPlayerVariables($characterConfig);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::INCUBATING)
            ->setDiseasePoint(1);

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $event = new PlayerCycleEvent($player, [EventEnum::NEW_CYCLE], new \DateTime());

        $this->subscriber->onPlayerNewCycle($event);

        $I->assertGreaterThan(0, $playerDisease->getDiseasePoint());

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
            'status' => DiseaseStatusEnum::ACTIVE,
        ]);

        $I->seeInRepository(RoomLog::class, [
            'playerInfo' => $playerInfo,
            'place' => $place->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => LogEnum::DISEASE_APPEAR,
        ]);
    }

    public function testOnPlayerCycleBitingSymptom(FunctionalTester $I): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $this->createProjects($I, $daedalus);

        /** @var Place $place */
        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $place,
        ]);
        $player->setPlayerVariables($characterConfig);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $otherPlayer */
        $otherPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $place,
        ]);
        $otherPlayer->setPlayerVariables($characterConfig);
        $otherPlayerInfo = new PlayerInfo($otherPlayer, $user, $characterConfig);

        $I->haveInRepository($otherPlayerInfo);
        $otherPlayer->setPlayerInfo($otherPlayerInfo);
        $I->refreshEntities($otherPlayer);

        $symptomConfig = new EventModifierConfig('biting_test');
        $symptomConfig
            ->setTargetEvent(PlayerCycleEvent::PLAYER_NEW_CYCLE)
            ->setApplyWhenTargeted(true)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::BITING);

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setModifierConfigs([$symptomConfig])
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10);
        $I->haveInRepository($playerDisease);
        $I->refreshEntities($player);
        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());

        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);
        $I->assertCount(1, $player->getModifiers());

        $event = new PlayerCycleEvent($player, [EventEnum::NEW_CYCLE], new \DateTime());

        $this->eventService->callEvent($event, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        $I->seeInRepository(RoomLog::class, [
            'playerInfo' => $playerInfo,
            'place' => $place->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => 'biting',
        ]);
    }

    public function testOnPlayerCycleDirtinessSymptom(FunctionalTester $I): void
    {
        /** @var StatusConfig $dirtyStatus */
        $dirtyStatus = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => PlayerStatusEnum::DIRTY]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$dirtyStatus])]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $this->createProjects($I, $daedalus);

        /** @var Place $place */
        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $place,
        ]);
        $player->setPlayerVariables($characterConfig);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $symptomConfig = $I->grabEntityFromRepository(EventModifierConfig::class, ['name' => 'cycle_dirtiness']);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setModifierConfigs([$symptomConfig])
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10);
        $I->haveInRepository($playerDisease);
        $I->refreshEntities($player);
        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());

        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);
        $I->assertCount(1, $player->getModifiers());

        $event = new PlayerCycleEvent($player, [EventEnum::NEW_CYCLE], new \DateTime());

        $this->eventService->callEvent($event, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        $I->seeInRepository(RoomLog::class, [
            'playerInfo' => $playerInfo,
            'place' => $place->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => SymptomEnum::DIRTINESS,
        ]);
        $I->assertCount(1, $player->getStatuses());
        $I->assertTrue($player->hasStatus(PlayerStatusEnum::DIRTY));
    }

    private function createProjects(FunctionalTester $I, Daedalus $daedalus): void
    {
        $projects = [ProjectName::BEAT_BOX];
        foreach ($projects as $project) {
            $config = $I->grabEntityFromRepository(ProjectConfig::class, ['name' => $project]);
            $project = new Project($config, $daedalus);
            $I->haveInRepository($project);
            $daedalus->addProject($project);
        }
    }
}
