<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class PersonalEquipmentConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const ITRACKIE = 'i_trackie';
    public const TALKIE_WALKIE = 'talkie_walkie';
    public const TRACKER = 'tracker';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $takeAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var Action $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        $talkieWalkie = new ItemConfig();
        $talkieWalkie
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setActions(new ArrayCollection([$takeAction, $examineAction, $repair25, $dropAction]))
            ->setIsPersonal(true)
        ;
        $manager->persist($talkieWalkie);

        $iTrackie = new ItemConfig();
        $iTrackie
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ITRACKIE)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setActions(new ArrayCollection([$takeAction, $examineAction, $repair25, $dropAction]))
            ->setIsPersonal(true)
        ;
        $manager->persist($iTrackie);

        $tracker = new ItemConfig();
        $tracker
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::TRACKER)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setActions(new ArrayCollection([$takeAction, $examineAction, $repair25, $dropAction]))
            ->setIsPersonal(true)
        ;
        $manager->persist($tracker);

        $manager->flush();

        $this->addReference(self::ITRACKIE, $iTrackie);
        $this->addReference(self::TALKIE_WALKIE, $talkieWalkie);
        $this->addReference(self::TRACKER, $tracker);
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
            GameConfigFixtures::class,
        ];
    }
}
