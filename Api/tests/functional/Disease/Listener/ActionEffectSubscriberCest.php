<?php

namespace Mush\Tests\functional\Disease\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Event\ActionEffectEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Listener\ActionEffectSubscriber;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

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
        $gameConfig = $I->have(GameConfig::class);
        $daedalus = $I->have(Daedalus::class, [
            'gameConfig' => $gameConfig,
        ]);

        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);
        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $place,
        ]);

        $I->refreshEntities($player);

        $gameItem = $this->createRation($I);
        $diseaseConfig = $this->createDiseaseForRation($daedalus, $gameItem->getEquipment()->getRationsMechanic(), 'diseaseName', true);

        $event = new ActionEffectEvent($player, $gameItem);

        $this->subscriber->onConsume($event);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
            'status' => DiseaseStatusEnum::INCUBATING,
        ]);
    }

    public function testOnConsumeImmediatDisease(FunctionalTester $I)
    {
        $gameConfig = $I->have(GameConfig::class);
        $daedalus = $I->have(Daedalus::class, [
            'gameConfig' => $gameConfig,
        ]);

        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);
        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $place,
        ]);

        $I->refreshEntities($player);

        $gameItem = $this->createRation($I);
        $diseaseConfig = $this->createDiseaseForRation($daedalus, $gameItem->getEquipment()->getRationsMechanic(), 'diseaseName', false);

        $event = new ActionEffectEvent($player, $gameItem);

        $this->subscriber->onConsume($event);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
            'status' => DiseaseStatusEnum::ACTIVE,
        ]);
    }

    public function testOnHealNonResistantDisease(FunctionalTester $I)
    {
        $gameConfig = $I->have(GameConfig::class);
        $daedalus = $I->have(Daedalus::class, [
            'gameConfig' => $gameConfig,
        ]);

        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);
        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $place,
        ]);

        $I->refreshEntities($player);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setName('someName');
        $I->haveInRepository($diseaseConfig);

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
        ;
        $I->haveInRepository($diseasePlayer);

        $event = new ActionEffectEvent($player, $player);

        $this->subscriber->onHeal($event);

        $I->dontSeeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
            'status' => DiseaseStatusEnum::ACTIVE,
        ]);
    }

    public function testOnHealResistantDisease(FunctionalTester $I)
    {
        $gameConfig = $I->have(GameConfig::class);
        $daedalus = $I->have(Daedalus::class, [
            'gameConfig' => $gameConfig,
        ]);

        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);
        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $place,
        ]);

        $I->refreshEntities($player);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setName('someName');
        $I->haveInRepository($diseaseConfig);

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setResistancePoint(1)
        ;
        $I->haveInRepository($diseasePlayer);

        $event = new ActionEffectEvent($player, $player);

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
        Ration $ration,
        string $diseaseName,
        bool $delayed = false
    ): DiseaseConfig {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setGameConfig($daedalus->getGameConfig())
            ->setName($diseaseName)
        ;

        $this->tester->haveInRepository($diseaseConfig);

        $consumableEffect = new ConsumableEffect();
        $consumableEffect
            ->setRation($ration)
            ->setDaedalus($daedalus)
        ;

        $this->tester->haveInRepository($consumableEffect);

        $consumableAttribute = new ConsumableDiseaseAttribute();
        $consumableAttribute
            ->setRate(100)
            ->setDisease($diseaseName)
            ->setConsumableEffect($consumableEffect)
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
        $I->haveInRepository($ration);

        $itemConfig = new ItemConfig();
        $itemConfig
            ->setName('itemName')
            ->setMechanics(new ArrayCollection([$ration]))
        ;

        $I->haveInRepository($itemConfig);

        $gameItem = new GameItem();
        $gameItem
            ->setName('itemName')
            ->setEquipment($itemConfig)
        ;

        $I->haveInRepository($gameItem);

        return $gameItem;
    }
}
