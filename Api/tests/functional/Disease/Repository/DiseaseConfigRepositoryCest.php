<?php

namespace functional\Disease\Repository;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
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
        $gameConfig = $I->have(GameConfig::class);
        $daedalus = $I->have(Daedalus::class, [
            'gameConfig' => $gameConfig,
        ]);

        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig->setPerishedFoodDiseases(['disease name' => 1])->setGameConfig($gameConfig);

        $I->haveInRepository($diseaseCauseConfig);

        $diseases = $this->repository->findCausesByDaedalus($daedalus);

        $I->assertEquals($diseases, $diseaseCauseConfig);
    }
}
