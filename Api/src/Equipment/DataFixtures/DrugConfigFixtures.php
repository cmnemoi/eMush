<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;

class DrugConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);
        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $consumeDrugAction */
        $consumeDrugAction = $this->getReference(ActionsFixtures::DRUG_CONSUME);
        /** @var Action $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        $actions = [$takeAction, $dropAction, $hideAction, $examineAction];

        $drugMechanic = new Drug();
        //  possibilities are stored as key, array value represent the probability to get the key value
        $drugMechanic
            ->setMoralPoints([0 => 97, -2 => 1, 1 => 1, 3 => 1])
            ->setActionPoints([0 => 98, 1 => 1, 3 => 1])
            ->setMovementPoints([0 => 98, 2 => 1, 4 => 1])
            ->addAction($consumeDrugAction)
            ->buildName('drug', GameConfigEnum::DEFAULT)
        ;

        foreach (GameDrugEnum::getAll() as $drugName) {
            $drug = new ItemConfig();
            $drug
                ->setEquipmentName($drugName)
                ->setIsStackable(true)
                ->setIsFireDestroyable(true)
                ->setIsFireBreakable(false)
                ->setMechanics([$drugMechanic])
                ->setActions($actions)
                ->buildName(GameConfigEnum::DEFAULT)
            ;
            $manager->persist($drug);
            $gameConfig->addEquipmentConfig($drug);
        }
        $manager->persist($drugMechanic);
        $manager->persist($gameConfig);

        // special drug for tests
        $prozacTest = new ItemConfig();
        $prozacTest
            ->setEquipmentName('prozac_test')
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$drugMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::TEST)
        ;
        $manager->persist($prozacTest);
        $gameConfig->addEquipmentConfig($prozacTest);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            ActionsFixtures::class,
        ];
    }
}
