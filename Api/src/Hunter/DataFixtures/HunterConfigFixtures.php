<?php

namespace Mush\Hunter\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;

class HunterConfigFixtures extends Fixture
{
    /**
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $asteroid = new HunterConfig();
        $asteroid
            ->setName(HunterEnum::ASTEROID . '_default')
            ->setHunterName(HunterEnum::ASTEROID)
            ->setInitialHealth(20)
            ->setInitialCharge(6)
            ->setInitialArmor(0)
            ->setMinDamage(0)
            ->setMaxDamage(0)
            ->setHitChance(100)
            ->setDodgeChance(20)
            ->setDrawCost(25)
            ->setMaxPerWave(2)
            ->setDrawWeight(1)
        ;
        $manager->persist($asteroid);

        $dice = new HunterConfig();
        $dice
            ->setName(HunterEnum::DICE . '_default')
            ->setHunterName(HunterEnum::DICE)
            ->setInitialHealth(30)
            ->setInitialCharge(0)
            ->setInitialArmor(1)
            ->setMinDamage(3)
            ->setMaxDamage(6)
            ->setHitChance(60)
            ->setDodgeChance(20)
            ->setDrawCost(30)
            ->setMaxPerWave(1)
            ->setDrawWeight(1)
        ;
        $manager->persist($dice);

        $hunter = new HunterConfig();
        $hunter
            ->setName(HunterEnum::HUNTER . '_default')
            ->setHunterName(HunterEnum::HUNTER)
            ->setInitialHealth(6)
            ->setInitialCharge(0)
            ->setInitialArmor(0)
            ->setMinDamage(2)
            ->setMaxDamage(4)
            ->setHitChance(80)
            ->setDodgeChance(50)
            ->setDrawCost(10)
            ->setMaxPerWave(null)
            ->setDrawWeight(10)
        ;
        $manager->persist($hunter);

        $spider = new HunterConfig();
        $spider
            ->setName(HunterEnum::SPIDER . '_default')
            ->setHunterName(HunterEnum::SPIDER)
            ->setInitialHealth(6)
            ->setInitialCharge(0)
            ->setInitialArmor(0)
            ->setMinDamage(1)
            ->setMaxDamage(3)
            ->setHitChance(80)
            ->setDodgeChance(50)
            ->setDrawCost(10)
            ->setMaxPerWave(null)
            ->setDrawWeight(10)
        ;
        $manager->persist($spider);

        $trax = new HunterConfig();
        $trax
            ->setName(HunterEnum::TRAX . '_default')
            ->setHunterName(HunterEnum::TRAX)
            ->setInitialHealth(10)
            ->setInitialCharge(0)
            ->setInitialArmor(0)
            ->setMinDamage(2)
            ->setMaxDamage(3)
            ->setHitChance(50)
            ->setDodgeChance(50)
            ->setDrawCost(20)
            ->setMaxPerWave(2)
            ->setDrawWeight(2)
        ;
        $manager->persist($trax);

        $gameConfig->setHunterConfigs(new ArrayCollection([
            $asteroid,
            $dice,
            $hunter,
            $spider,
            $trax,
        ]));

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
