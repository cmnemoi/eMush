<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Hunter\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Service\HunterNormalizerHelperInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class HunterNormalizerHelperCest extends AbstractFunctionalTest
{
    private HunterNormalizerHelperInterface $hunterNormalizerHelper;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->hunterNormalizerHelper = $I->grabService(HunterNormalizerHelperInterface::class);
    }

    public function testGetHuntersToNormalizeWithSeventeenHuntersReturnSeventeenHunters(FunctionalTester $I): void
    {
        // given 17 simple hunters are attacking
        for ($i = 0; $i < 17; ++$i) {
            $hunter = $this->createHunterFromName($this->daedalus, HunterEnum::HUNTER);
            $I->haveInRepository($hunter);
        }

        // when I call getHuntersToNormalize
        $hunters = $this->hunterNormalizerHelper->getHuntersToNormalize($this->daedalus);

        // then I get 17 hunters
        $I->assertCount(17, $hunters->getAllHuntersByType(HunterEnum::HUNTER));
    }

    public function testGetHuntersToNormalizeWithEighteenHunterReturnSeventeenHunters(FunctionalTester $I): void
    {
        // given 17 simple hunters are attacking
        for ($i = 0; $i < 18; ++$i) {
            $hunter = $this->createHunterFromName($this->daedalus, HunterEnum::HUNTER);
            $I->haveInRepository($hunter);
        }

        // when I call getHuntersToNormalize
        $hunters = $this->hunterNormalizerHelper->getHuntersToNormalize($this->daedalus);

        // then I get 17 hunters
        $I->assertCount(17, $hunters->getAllHuntersByType(HunterEnum::HUNTER));
    }

    public function testGetHuntersToNormalizeWithSeventeenHuntersAndOneTypeOfEachAdvancedHunterReturnsAtLeastOneHunterOfEachType(FunctionalTester $I): void
    {
        // given 17 simple hunters are attacking
        for ($i = 0; $i < 17; ++$i) {
            $hunter = $this->createHunterFromName($this->daedalus, HunterEnum::HUNTER);
            $I->haveInRepository($hunter);
        }

        // given one type of each advanced hunter is attacking
        foreach (HunterEnum::getAdvancedHunters() as $advancedHunterName) {
            $hunter = $this->createHunterFromName($this->daedalus, $advancedHunterName);
            $I->haveInRepository($hunter);
        }

        // when I call getHuntersToNormalize
        $hunters = $this->hunterNormalizerHelper->getHuntersToNormalize($this->daedalus);

        // then I get at least one hunter of each type
        $I->assertGreaterThanOrEqual(1, $hunters->getAllHuntersByType(HunterEnum::ASTEROID)->count());
        $I->assertGreaterThanOrEqual(1, $hunters->getAllHuntersByType(HunterEnum::SPIDER)->count());
        $I->assertGreaterThanOrEqual(1, $hunters->getAllHuntersByType(HunterEnum::TRAX)->count());
        $I->assertGreaterThanOrEqual(1, $hunters->getAllHuntersByType(HunterEnum::DICE)->count());
        $I->assertGreaterThanOrEqual(1, $hunters->getAllHuntersByType(HunterEnum::HUNTER)->count());
    }

    public function testGetHuntersToNormalizeWithFiveHuntersAndTwoTypeOfEachAdvancedHunterReturnsFiveHuntersAndTwoAdvancedHunterByType(FunctionalTester $I): void
    {
        // given 5 simple hunters are attacking
        for ($i = 0; $i < 5; ++$i) {
            $hunter = $this->createHunterFromName($this->daedalus, HunterEnum::HUNTER);
            $I->haveInRepository($hunter);
        }

        $I->assertCount(5, $this->daedalus->getAttackingHunters());

        // given two type of each advanced hunter is attacking
        foreach (HunterEnum::getAdvancedHunters() as $advancedHunterName) {
            for ($i = 0; $i < 2; ++$i) {
                $hunter = $this->createHunterFromName($this->daedalus, $advancedHunterName);
                $I->haveInRepository($hunter);
            }
        }

        // when I call getHuntersToNormalize
        $hunters = $this->hunterNormalizerHelper->getHuntersToNormalize($this->daedalus);

        // then I get 5 hunters
        $I->assertCount(5, $hunters->getAllHuntersByType(HunterEnum::HUNTER));

        // then I get 2 asteroids
        $I->assertCount(2, $hunters->getAllHuntersByType(HunterEnum::ASTEROID));

        // then I get 2 spiders
        $I->assertCount(2, $hunters->getAllHuntersByType(HunterEnum::SPIDER));

        // then I get 2 trax
        $I->assertCount(2, $hunters->getAllHuntersByType(HunterEnum::TRAX));

        // then I get 2 dices
        $I->assertCount(2, $hunters->getAllHuntersByType(HunterEnum::DICE));
    }

    public function testGetHuntersToNormalizePrioritizesLowHealthHunters(FunctionalTester $I): void
    {
        // given I have 18 simple hunters are attacking
        for ($i = 0; $i < 18; ++$i) {
            $hunter = $this->createHunterFromName($this->daedalus, HunterEnum::HUNTER);
            $I->haveInRepository($hunter);
        }

        // given I have the last one has 1 health
        $lowHealthHunter = $this->daedalus->getAttackingHunters()->last();
        $lowHealthHunter->setHealth(1);

        // when I call getHuntersToNormalize
        $hunters = $this->hunterNormalizerHelper->getHuntersToNormalize($this->daedalus);

        // then lowHealthHunter is in the list
        $I->assertTrue($hunters->getAllHuntersByType(HunterEnum::HUNTER)->contains($lowHealthHunter));
    }

    private function createHunterFromName(Daedalus $daedalus, string $hunterName): Hunter
    {
        /** @var HunterConfig $hunterConfig */
        $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterName);
        if (!$hunterConfig) {
            throw new \Exception("Hunter config not found for hunter name $hunterName");
        }

        $hunter = new Hunter($hunterConfig, $daedalus);
        $hunter->setHunterVariables($hunterConfig);
        $daedalus->addHunter($hunter);

        return $hunter;
    }
}
