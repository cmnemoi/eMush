<?php

namespace Mush\Tests\functional\Disease\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\ConsumableDisease;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Listener\ActionEffectSubscriber;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class ActionEffectSubscriberCest
{
    private FunctionalTester $tester;

    private ActionEffectSubscriber $subscriber;

    public function _before(FunctionalTester $I)
    {
        $this->tester = $I;

        $this->subscriber = $I->grabService(ActionEffectSubscriber::class);
    }

    public function testOnConsumeDelayedDisease(FunctionalTester $I)
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

        $gameItem = $this->createRation($I);
        $diseaseConfig = $this->createDiseaseForRation($daedalus, $gameItem->getName(), 'diseaseName', true);

        $gameConfig->addDiseaseConfig($diseaseConfig);

        $event = new ApplyEffectEvent(
            $player,
            $gameItem,
            VisibilityEnum::HIDDEN,
            [ActionEnum::CONSUME],
            new \DateTime()
        );

        $this->subscriber->onConsume($event);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
            'status' => DiseaseStatusEnum::INCUBATING,
        ]);
    }

    public function testOnConsumeImmediatDisease(FunctionalTester $I)
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

        $gameItem = $this->createRation($I);
        $diseaseConfig = $this->createDiseaseForRation($daedalus, $gameItem->getName(), 'diseaseName', false);

        $gameConfig->addDiseaseConfig($diseaseConfig);

        $event = new ApplyEffectEvent(
            $player,
            $gameItem,
            VisibilityEnum::HIDDEN,
            [ActionEnum::CONSUME],
            new \DateTime()
        );

        $this->subscriber->onConsume($event);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
            'status' => DiseaseStatusEnum::ACTIVE,
        ]);
    }

    public function testOnHealNonResistantDisease(FunctionalTester $I)
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
            'name' => RoomEnum::MEDLAB,
        ]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
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
            ->setDiseaseName('someName')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
        ;
        $I->haveInRepository($diseasePlayer);

        $event = new ApplyEffectEvent(
            $player,
            $player,
            VisibilityEnum::HIDDEN,
            [ActionEnum::HEAL],
            new \DateTime()
        );

        $this->subscriber->onHeal($event);

        $I->dontSeeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
            'status' => DiseaseStatusEnum::ACTIVE,
        ]);
    }

    public function testOnHealResistantDisease(FunctionalTester $I)
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
            'name' => RoomEnum::MEDLAB,
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
            ->setDiseaseName('someName')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setResistancePoint(1)
        ;
        $I->haveInRepository($diseasePlayer);

        $event = new ApplyEffectEvent(
            $player,
            $player,
            VisibilityEnum::HIDDEN,
            [ActionEnum::HEAL],
            new \DateTime()
        );

        $this->subscriber->onHeal($event);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
            'status' => DiseaseStatusEnum::ACTIVE,
            'resistancePoint' => 0,
        ]);
    }

    private function createDiseaseForRation(
        Daedalus $daedalus,
        string $rationName,
        string $diseaseName,
        bool $delayed = false,
    ): DiseaseConfig {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName($diseaseName)
            ->buildName(GameConfigEnum::TEST)
        ;
        $this->tester->haveInRepository($diseaseConfig);

        $consumableDisease = new ConsumableDisease();
        $consumableDisease
            ->setName($rationName)
            ->setDaedalus($daedalus)
        ;

        $this->tester->haveInRepository($consumableDisease);

        $consumableAttribute = new ConsumableDiseaseAttribute();
        $consumableAttribute
            ->setRate(100)
            ->setDisease($diseaseName)
            ->setConsumableDisease($consumableDisease)
        ;

        if ($delayed) {
            $consumableAttribute
                ->setDelayMin(10)
                ->setDelayLength(10)
            ;
        }

        $this->tester->haveInRepository($consumableAttribute);

        return $diseaseConfig;
    }

    private function createRation(FunctionalTester $I): GameItem
    {
        $ration = new Ration();
        $ration->setName('ration_test');
        $I->haveInRepository($ration);

        $itemConfig = new ItemConfig();
        $itemConfig
            ->setEquipmentName('itemName')
            ->setMechanics(new ArrayCollection([$ration]))
            ->buildName(GameConfigEnum::TEST)
        ;

        $I->haveInRepository($itemConfig);

        $gameItem = new GameItem(new Place());
        $gameItem
            ->setName('itemName')
            ->setEquipment($itemConfig)
        ;

        return $gameItem;
    }
}
