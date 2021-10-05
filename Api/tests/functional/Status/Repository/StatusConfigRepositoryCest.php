<?php

namespace Mush\Tests\Status\Repository;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Repository\StatusConfigRepository;

class StatusConfigRepositoryCest
{
    private StatusConfigRepository $repository;

    public function _before(FunctionalTester $I)
    {
        $this->repository = $I->grabService(StatusConfigRepository::class);
    }

    public function testFindByName(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        $statusConfig = new StatusConfig();
        $statusConfig->setName(EquipmentStatusEnum::HAZARDOUS)->setGameConfig($gameConfig);
        $I->haveInRepository($statusConfig);

        $result = $this->repository->findByNameAndDaedalus(EquipmentStatusEnum::HAZARDOUS, $daedalus);

        $I->assertEquals($statusConfig, $result);
    }
}
