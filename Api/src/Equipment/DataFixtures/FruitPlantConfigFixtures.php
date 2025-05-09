<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ExtraEffectEnum;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\BreakableTypeEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;

class FruitPlantConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ActionConfig $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);

        /** @var ActionConfig $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        /** @var ActionConfig $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);

        /** @var ActionConfig $consumeRationAction */
        $consumeRationAction = $this->getReference(ActionsFixtures::RATION_CONSUME);

        /** @var ActionConfig $transplantAction */
        $transplantAction = $this->getReference(ActionsFixtures::TRANSPLANT);

        /** @var ActionConfig $treatAction */
        $treatAction = $this->getReference(ActionsFixtures::TREAT_PLANT);

        /** @var ActionConfig $waterAction */
        $waterAction = $this->getReference(ActionsFixtures::WATER_PLANT);

        /** @var ActionConfig $graftAction */
        $graftAction = $this->getReference(ActionEnum::GRAFT->value);

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var ActionConfig $mixRationSporeAction */
        $mixRationSporeAction = $this->getReference(ActionEnum::MIX_RATION_SPORE->value);

        /** @var ArrayCollection $actions */
        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        /** @var ArrayCollection $plantActions */
        $plantActions = new ArrayCollection([$treatAction, $waterAction]);

        /** @var ArrayCollection $fruitActions */
        $fruitActions = new ArrayCollection([$consumeRationAction, $transplantAction, $graftAction, $mixRationSporeAction]);

        $bananaMechanic = new Fruit();
        $bananaMechanic
            ->setPlantName(GamePlantEnum::BANANA_TREE)
            ->setActionPoints([1 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([1 => 1])
            ->setMoralPoints([1 => 1])
            ->setSatiety(1)
            ->setActions($fruitActions)
            ->buildName(EquipmentMechanicEnum::FRUIT . '_' . GameFruitEnum::BANANA, GameConfigEnum::DEFAULT);

        $banana = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameFruitEnum::BANANA));
        $banana
            ->setMechanics([$bananaMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($bananaMechanic);
        $manager->persist($banana);

        $gameConfig->addEquipmentConfig($banana);

        $bananaTreeMechanic = new Plant();
        //  possibilities are stored as key, array value represent the probability to get the key value
        $bananaTreeMechanic
            ->setFruitName($banana->getEquipmentName())
            ->setMaturationTime([36 => 1])
            ->setOxygen([1 => 1])
            ->setActions($plantActions)
            ->buildName(EquipmentMechanicEnum::PLANT . '_' . GamePlantEnum::BANANA_TREE, GameConfigEnum::DEFAULT);

        $bananaTree = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GamePlantEnum::BANANA_TREE));
        $bananaTree
            ->setMechanics([$bananaTreeMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($bananaTreeMechanic);
        $manager->persist($bananaTree);
        $gameConfig->addEquipmentConfig($bananaTree);

        $alienFruitPlant = [
            GameFruitEnum::CREEPNUT => GamePlantEnum::CREEPIST,
            GameFruitEnum::MEZTINE => GamePlantEnum::CACTAX,
            GameFruitEnum::GUNTIFLOP => GamePlantEnum::BIFFLON,
            GameFruitEnum::PLOSHMINA => GamePlantEnum::PULMMINAGRO,
            GameFruitEnum::PRECATI => GamePlantEnum::PRECATUS,
            GameFruitEnum::BOTTINE => GamePlantEnum::BUTTALIEN,
            GameFruitEnum::FRAGILANE => GamePlantEnum::PLATACIA,
            GameFruitEnum::ANEMOLE => GamePlantEnum::TUBILISCUS,
            GameFruitEnum::PENICRAFT => GamePlantEnum::GRAAPSHOOT,
            GameFruitEnum::KUBINUS => GamePlantEnum::FIBONICCUS,
            GameFruitEnum::CALEBOOT => GamePlantEnum::MYCOPIA,
            GameFruitEnum::FILANDRA => GamePlantEnum::ASPERAGUNK,
        ];

        foreach ($alienFruitPlant as $fruitName => $plantName) {
            $alienFruitMechanic = new Fruit();
            $alienFruitMechanic
                ->setPlantName($plantName)
                ->setActionPoints([1 => 100])
                ->setMoralPoints([0 => 30, 1 => 70])
                ->setExtraEffects([ExtraEffectEnum::EXTRA_PA_GAIN => 50])
                ->setSatiety(1)
                ->setActions($fruitActions)
                ->buildName(EquipmentMechanicEnum::FRUIT . '_' . $fruitName, GameConfigEnum::DEFAULT);
            $manager->persist($alienFruitMechanic);

            $alienFruit = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName($fruitName));
            $alienFruit
                ->setMechanics([$alienFruitMechanic])
                ->setActionConfigs($actions)
                ->buildName(GameConfigEnum::DEFAULT);
            $manager->persist($alienFruit);

            $alienPlantMechanic = new Plant();
            $alienPlantMechanic
                ->setFruitName($alienFruit->getEquipmentName())
                ->setMaturationTime([8 => 1])
                ->setOxygen([1 => 1])
                ->setActions($plantActions)
                ->buildName(EquipmentMechanicEnum::PLANT . '_' . $plantName, GameConfigEnum::DEFAULT);

            $alienPlant = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName($plantName));
            $alienPlant
                ->setMechanics([$alienPlantMechanic])
                ->setActionConfigs($actions)
                ->buildName(GameConfigEnum::DEFAULT);
            $manager->persist($alienPlantMechanic);
            $manager->persist($alienPlant);

            $gameConfig->addEquipmentConfig($alienFruit)->addEquipmentConfig($alienPlant);
        }

        $jumpkinMechanic = new Fruit();
        $jumpkinMechanic
            ->setPlantName(GamePlantEnum::BUMPJUMPKIN)
            ->setActionPoints([3])
            ->setMovementPoints([0])
            ->setHealthPoints([1])
            ->setMoralPoints([1])
            ->setActions($fruitActions)
            ->buildName(EquipmentMechanicEnum::FRUIT . '_' . GameFruitEnum::JUMPKIN, GameConfigEnum::DEFAULT);

        $jumpkin = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameFruitEnum::JUMPKIN));
        $jumpkin
            ->setMechanics([$jumpkinMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($jumpkinMechanic);
        $manager->persist($jumpkin);
        $gameConfig->addEquipmentConfig($jumpkin);

        $bumpjumpkinMechanic = new Plant();
        $bumpjumpkinMechanic
            ->setFruitName($jumpkin->getEquipmentName())
            ->setMaturationTime([8 => 1])
            ->setOxygen([1 => 1])
            ->setActions($plantActions)
            ->buildName(EquipmentMechanicEnum::PLANT . '_' . GamePlantEnum::BUMPJUMPKIN, GameConfigEnum::DEFAULT);

        $bumpjumpkin = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GamePlantEnum::BUMPJUMPKIN));
        $bumpjumpkin
            ->setMechanics([$bumpjumpkinMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($bumpjumpkinMechanic);
        $manager->persist($bumpjumpkin);
        $gameConfig->addEquipmentConfig($bumpjumpkin);

        $fluHealerMechanic = new Fruit();
        $fluHealerMechanic
            ->setPlantName('flu_healer_plant_test')
            ->setActionPoints([0 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setActions($fruitActions)
            ->buildName(EquipmentMechanicEnum::FRUIT . '_flu_healer_test', GameConfigEnum::DEFAULT);

        $fluHealer = new ItemConfig();
        $fluHealer
            ->setEquipmentName('flu_healer_test')
            ->setIsStackable(true)
            ->setBreakableType(BreakableTypeEnum::DESTROY_ON_BREAK)
            ->setMechanics([$fluHealerMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::TEST);
        $manager->persist($fluHealerMechanic);
        $manager->persist($fluHealer);
        $gameConfig->addEquipmentConfig($fluHealer);

        $manager->persist($gameConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            GameConfigFixtures::class,
        ];
    }
}
