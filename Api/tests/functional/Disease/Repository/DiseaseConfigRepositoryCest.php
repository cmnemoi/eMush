<?php

namespace functional\Disease\Repository;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\DiseaseCause;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Enum\DiseaseEnum;

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

        $cause = new DiseaseCause();
        $cause->setName(DiseaseCauseEnum::SPOILED_FOOD);
        $I->haveInRepository($cause);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName(DiseaseEnum::TAPEWORM)
            ->setGameConfig($gameConfig)
            ->setCauses(new ArrayCollection([$cause]))
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

        $cause = new DiseaseCause();
        $cause->setName(DiseaseCauseEnum::SPOILED_FOOD);
        $I->haveInRepository($cause);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName(DiseaseEnum::TAPEWORM)
            ->setGameConfig($gameConfig)
            ->setCauses(new ArrayCollection([$cause]))
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2
            ->setName(DiseaseEnum::GASTROENTERIS)
            ->setGameConfig($gameConfig)
        ;

        $I->haveInRepository($diseaseConfig2);

        $diseases = $this->repository->findByCauses(DiseaseCauseEnum::SPOILED_FOOD, $daedalus);

        $I->assertCount(1, $diseases);
        $I->assertContains($diseaseConfig, $diseases);
    }
}
