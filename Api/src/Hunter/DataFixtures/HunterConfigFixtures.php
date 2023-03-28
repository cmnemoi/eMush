<?php

namespace Mush\Hunter\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\ProbaCollection;
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

        $asteroidDamageRange = $this->buildUniformDamageRange(6, 6);
        $asteroid = new HunterConfig();
        $asteroid
            ->setName(HunterEnum::ASTEROID . '_default')
            ->setHunterName(HunterEnum::ASTEROID)
            ->setInitialHealth(20)
            ->setInitialCharge(6)
            ->setInitialArmor(0)
            ->setDamageRange($asteroidDamageRange)
            ->setHitChance(100)
            ->setDodgeChance(20)
            ->setDrawCost(25)
            ->setMaxPerWave(2)
            ->setDrawWeight(1)
        ;
        $manager->persist($asteroid);

        $diceDamageRange = $this->buildUniformDamageRange(3, 6);
        $dice = new HunterConfig();
        $dice
            ->setName(HunterEnum::DICE . '_default')
            ->setHunterName(HunterEnum::DICE)
            ->setInitialHealth(30)
            ->setInitialCharge(0)
            ->setInitialArmor(1)
            ->setDamageRange($diceDamageRange)
            ->setHitChance(60)
            ->setDodgeChance(20)
            ->setDrawCost(30)
            ->setMaxPerWave(1)
            ->setDrawWeight(1)
        ;
        $manager->persist($dice);

        $hunterDamageRange = $this->buildUniformDamageRange(2, 4);
        $hunter = new HunterConfig();
        $hunter
            ->setName(HunterEnum::HUNTER . '_default')
            ->setHunterName(HunterEnum::HUNTER)
            ->setInitialHealth(6)
            ->setInitialCharge(0)
            ->setInitialArmor(0)
            ->setDamageRange($hunterDamageRange)
            ->setHitChance(80)
            ->setDodgeChance(50)
            ->setDrawCost(10)
            ->setMaxPerWave(null)
            ->setDrawWeight(10)
        ;
        $manager->persist($hunter);

        $spiderDamageRange = $this->buildUniformDamageRange(1, 3);
        $spider = new HunterConfig();
        $spider
            ->setName(HunterEnum::SPIDER . '_default')
            ->setHunterName(HunterEnum::SPIDER)
            ->setInitialHealth(6)
            ->setInitialCharge(0)
            ->setInitialArmor(0)
            ->setDamageRange($spiderDamageRange)
            ->setHitChance(80)
            ->setDodgeChance(50)
            ->setDrawCost(10)
            ->setMaxPerWave(null)
            ->setDrawWeight(10)
        ;
        $manager->persist($spider);

        $traxDamageRange = $this->buildUniformDamageRange(2, 3);
        $trax = new HunterConfig();
        $trax
            ->setName(HunterEnum::TRAX . '_default')
            ->setHunterName(HunterEnum::TRAX)
            ->setInitialHealth(10)
            ->setInitialCharge(0)
            ->setInitialArmor(0)
            ->setDamageRange($traxDamageRange)
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

    private function buildUniformDamageRange(int $min, int $max): ProbaCollection
    {
        $damageRange = new ProbaCollection();
        for ($i = $min; $i <= $max; ++$i) {
            $damageRange->setElementProbability($i, 1);
        }

        return $damageRange;
    }
}
