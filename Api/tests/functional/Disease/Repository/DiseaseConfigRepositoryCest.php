<?php

namespace functional\Disease\Repository;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Game\Entity\GameConfig;

class DiseaseConfigRepositoryCest
{
    private DiseaseConfigRepository $repository;

    public function _before(FunctionalTester $I)
    {
        $this->repository = $I->grabService(DiseaseConfigRepository::class);
    }

    public function testFindByCauseInexistantCause(FunctionalTester $I)
    {
        $gameConfig = $I->have(GameConfig::class);
        $daedalus = $I->have(Daedalus::class, [
            'gameConfig' => $gameConfig,
        ]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName(DiseaseEnum::TAPEWORM)
            ->setGameConfig($gameConfig)
            ->setCauses([DiseaseCauseEnum::PERISHED_FOOD])
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2
            ->setName(DiseaseEnum::GASTROENTERIS)
            ->setGameConfig($gameConfig)
        ;

        $I->haveInRepository($diseaseConfig2);

        $diseases = $this->repository->findByCauses('inexistant', $daedalus);

        $I->assertEmpty($diseases);
    }

    public function testFindByCause(FunctionalTester $I)
    {
        $gameConfig = $I->have(GameConfig::class);
        $daedalus = $I->have(Daedalus::class, [
            'gameConfig' => $gameConfig,
        ]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName(DiseaseEnum::TAPEWORM)
            ->setGameConfig($gameConfig)
            ->setCauses([DiseaseCauseEnum::PERISHED_FOOD])
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2
            ->setName(DiseaseEnum::GASTROENTERIS)
            ->setGameConfig($gameConfig)
        ;

        $I->haveInRepository($diseaseConfig2);

        $diseases = $this->repository->findByCauses(DiseaseCauseEnum::PERISHED_FOOD, $daedalus);

        $I->assertCount(1, $diseases);
        $I->assertContains($diseaseConfig, $diseases);
    }
}
