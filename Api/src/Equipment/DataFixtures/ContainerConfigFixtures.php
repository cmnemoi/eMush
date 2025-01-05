<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Container;
use Mush\Equipment\Enum\ContainerContentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;

class ContainerConfigFixtures extends Fixture implements DependentFixtureInterface
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

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        $actions = [$takeAction, $dropAction, $hideAction, $examineAction];

        /** @var ActionConfig $openContainerCost0 */
        $openContainerCost0 = $this->getReference('open_container_cost_0');

        $coffeeThermosMechanic = new Container();
        $coffeeThermosMechanic
            ->addAction($openContainerCost0)
            ->buildName('container_' . ItemEnum::COFFEE_THERMOS, GameConfigEnum::DEFAULT)
            ->setContents(ContainerContentEnum::COFFEE_THERMOS_CONTENT);

        $coffeeThermos = new ItemConfig();
        $coffeeThermos
            ->setEquipmentName(ItemEnum::COFFEE_THERMOS)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics([$coffeeThermosMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($coffeeThermosMechanic);
        $manager->persist($coffeeThermos);

        $gameConfig->addEquipmentConfig($coffeeThermos);

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
