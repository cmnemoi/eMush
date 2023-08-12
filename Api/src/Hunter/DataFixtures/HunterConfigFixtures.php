<?php

namespace Mush\Hunter\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\DifficultyConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\ProbaCollection;
use Mush\Game\Enum\DifficultyEnum;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\Entity\Config\StatusConfig;

class HunterConfigFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $this->getReference(DifficultyConfigFixtures::DEFAULT_DIFFICULTY_CONFIG);

        /** @var StatusConfig $asteroidCharge */
        $asteroidCharge = $this->getReference(ChargeStatusFixtures::ASTEROID_CHARGE);
        $asteroidDamageRange = $this->buildUniformDamageRange(0, 0);
        $asteroid = new HunterConfig();
        $asteroid
            ->setName(HunterEnum::ASTEROID . '_default')
            ->setHunterName(HunterEnum::ASTEROID)
            ->setInitialHealth(20)
            ->setInitialStatuses(new ArrayCollection([$asteroidCharge]))
            ->setDamageRange($asteroidDamageRange)
            ->setHitChance(100)
            ->setDodgeChance(20)
            ->setDrawCost(25)
            ->setMaxPerWave(2)
            ->setDrawWeight(1)
            ->setSpawnDifficulty($difficultyConfig->getDifficultyModes()->get(DifficultyEnum::HARD))
            ->setScrapDropTable(new ProbaCollection([
                ItemEnum::METAL_SCRAPS => 2,
                ItemEnum::PLASTIC_SCRAPS => 1,
            ]))
            ->setNumberOfDroppedScrap([
                1 => 1,
                2 => 1,
                3 => 1,
            ])
            ->setActions([
                ActionEnum::SHOOT_HUNTER,
                ActionEnum::SHOOT_HUNTER_PATROL_SHIP,
            ])
        ;
        $manager->persist($asteroid);

        /** @var StatusConfig $hunterCharge */
        $hunterCharge = $this->getReference(ChargeStatusFixtures::HUNTER_CHARGE);
        $diceDamageRange = $this->buildUniformDamageRange(3, 6);
        $dice = new HunterConfig();
        $dice
            ->setName(HunterEnum::DICE . '_default')
            ->setHunterName(HunterEnum::DICE)
            ->setInitialHealth(30)
            ->setInitialStatuses(new ArrayCollection([$hunterCharge]))
            ->setDamageRange($diceDamageRange)
            ->setHitChance(60)
            ->setDodgeChance(20)
            ->setDrawCost(30)
            ->setMaxPerWave(1)
            ->setDrawWeight(1)
            ->setSpawnDifficulty($difficultyConfig->getDifficultyModes()->get(DifficultyEnum::VERY_HARD))
            ->setScrapDropTable(new ProbaCollection([
                ItemEnum::METAL_SCRAPS => 2,
                ToolItemEnum::SPACE_CAPSULE => 2,
                ItemEnum::PLASTIC_SCRAPS => 1,
            ]))
            ->setNumberOfDroppedScrap([
                1 => 1,
                2 => 1,
                3 => 1,
                4 => 1,
            ])
            ->setActions([
                ActionEnum::SHOOT_HUNTER,
                ActionEnum::SHOOT_HUNTER_PATROL_SHIP,
            ])
        ;
        $manager->persist($dice);

        $hunterDamageRange = $this->buildUniformDamageRange(2, 4);
        $hunter = new HunterConfig();
        $hunter
            ->setName(HunterEnum::HUNTER . '_default')
            ->setHunterName(HunterEnum::HUNTER)
            ->setInitialHealth(6)
            ->setInitialStatuses(new ArrayCollection([$hunterCharge]))
            ->setDamageRange($hunterDamageRange)
            ->setHitChance(80)
            ->setDodgeChance(50)
            ->setDrawCost(10)
            ->setMaxPerWave(null)
            ->setDrawWeight(10)
            ->setSpawnDifficulty($difficultyConfig->getDifficultyModes()->get(DifficultyEnum::NORMAL))
            ->setScrapDropTable(new ProbaCollection([
                ItemEnum::METAL_SCRAPS => 2,
                ToolItemEnum::SPACE_CAPSULE => 1,
                ItemEnum::PLASTIC_SCRAPS => 1,
            ]))
            ->setNumberOfDroppedScrap([
                1 => 1,
                2 => 1,
            ])
            ->setActions([
                ActionEnum::SHOOT_HUNTER,
                ActionEnum::SHOOT_HUNTER_PATROL_SHIP,
            ])
        ;
        $manager->persist($hunter);

        $spiderDamageRange = $this->buildUniformDamageRange(1, 3);
        $spider = new HunterConfig();
        $spider
            ->setName(HunterEnum::SPIDER . '_default')
            ->setHunterName(HunterEnum::SPIDER)
            ->setInitialHealth(6)
            ->setInitialStatuses(new ArrayCollection([$hunterCharge]))
            ->setDamageRange($spiderDamageRange)
            ->setHitChance(80)
            ->setDodgeChance(50)
            ->setDrawCost(10)
            ->setMaxPerWave(null)
            ->setDrawWeight(10)
            ->setSpawnDifficulty($difficultyConfig->getDifficultyModes()->get(DifficultyEnum::HARD))
            ->setScrapDropTable(new ProbaCollection([
                ItemEnum::METAL_SCRAPS => 2,
                ToolItemEnum::SPACE_CAPSULE => 2,
                ItemEnum::PLASTIC_SCRAPS => 1,
            ]))
            ->setNumberOfDroppedScrap([
                1 => 1,
                2 => 1,
            ])
            ->setActions([
                ActionEnum::SHOOT_HUNTER,
                ActionEnum::SHOOT_HUNTER_PATROL_SHIP,
            ])
        ;
        $manager->persist($spider);

        $traxDamageRange = $this->buildUniformDamageRange(2, 3);
        $trax = new HunterConfig();
        $trax
            ->setName(HunterEnum::TRAX . '_default')
            ->setHunterName(HunterEnum::TRAX)
            ->setInitialHealth(10)
            ->setInitialStatuses(new ArrayCollection([$hunterCharge]))
            ->setDamageRange($traxDamageRange)
            ->setHitChance(50)
            ->setDodgeChance(50)
            ->setDrawCost(20)
            ->setMaxPerWave(2)
            ->setDrawWeight(2)
            ->setSpawnDifficulty($difficultyConfig->getDifficultyModes()->get(DifficultyEnum::VERY_HARD))
            ->setScrapDropTable(new ProbaCollection([
                ItemEnum::METAL_SCRAPS => 2,
                ToolItemEnum::SPACE_CAPSULE => 1,
                ItemEnum::PLASTIC_SCRAPS => 1,
            ]))
            ->setNumberOfDroppedScrap([
                1 => 1,
                2 => 1,
                3 => 1,
            ])
            ->setActions([
                ActionEnum::SHOOT_HUNTER,
                ActionEnum::SHOOT_HUNTER_PATROL_SHIP,
            ])
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
            ChargeStatusFixtures::class,
            DifficultyConfigFixtures::class,
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
