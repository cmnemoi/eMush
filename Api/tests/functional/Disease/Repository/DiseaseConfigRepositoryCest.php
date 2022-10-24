<?php

namespace functional\Disease\Repository;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Repository\DiseaseCausesConfigRepository;
use Mush\Game\Entity\GameConfig;

class DiseaseConfigRepositoryCest
{
    private DiseaseCausesConfigRepository $repository;

    public function _before(FunctionalTester $I)
    {
        $this->repository = $I->grabService(DiseaseCausesConfigRepository::class);
    }

    public function testCauseByDaedalus(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'gameConfig' => $gameConfig,
        ]);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setName(DiseaseCauseEnum::CYCLE)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseCause);

        $diseaseCause2 = new DiseaseCauseConfig();
        $diseaseCause2
            ->setName(DiseaseCauseEnum::PERISHED_FOOD)
            ->setDiseases(
                [DiseaseEnum::MUSH_ALLERGY => 1]
            )
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseCause2);

        $diseases = $this->repository->findCausesByDaedalus(DiseaseCauseEnum::CYCLE, $daedalus);

        $I->assertEquals($diseases, $diseaseCause);
    }
}
