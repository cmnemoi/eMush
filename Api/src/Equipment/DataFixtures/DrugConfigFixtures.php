<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class DrugConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);
        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $takeAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $consumeDrugAction */
        $consumeDrugAction = $this->getReference(ActionsFixtures::DRUG_CONSUME);
        /** @var Action $buildAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        $drugMechanic = new Drug();
        //  possibilities are stored as key, array value represent the probability to get the key value
        $drugMechanic
            ->setMoralPoints([0 => 97, -2 => 1, 1 => 1, 3 => 1])
            ->setActionPoints([0 => 98, 1 => 1, 3 => 1])
            ->setMovementPoints([0 => 98, 2 => 1, 4 => 1])
            ->addAction($consumeDrugAction)
        ;

        foreach (GameDrugEnum::getAll() as $drugName) {
            $drug = new ItemConfig();
            $drug
                ->setGameConfig($gameConfig)
                ->setName($drugName)
                ->setIsStackable(true)
                ->setIsFireDestroyable(true)
                ->setIsFireBreakable(false)
                ->setMechanics(new ArrayCollection([$drugMechanic]))
                ->setActions($actions)
            ;
            $manager->persist($drug);
        }
        $manager->persist($drugMechanic);

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
