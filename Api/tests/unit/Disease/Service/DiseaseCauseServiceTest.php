<?php

namespace Mush\Test\Disease\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Service\DiseaseCauseService;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;

class DiseaseCauseServiceTest extends TestCase
{
    private DiseaseCauseService $diseaseCauseService;

    /** @var PlayerDiseaseService | Mockery\Mock */
    private PlayerDiseaseService $playerDiseaseService;

    /**
     * @before
     */
    public function before()
    {
        $this->playerDiseaseService = Mockery::mock(PlayerDiseaseService::class);

        $this->diseaseCauseService = new DiseaseCauseService(
            $this->playerDiseaseService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testSpoiledFood()
    {
        $daedalus = new Daedalus();

        $player = new Player();
        $player->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment();

        $this->playerDiseaseService
            ->shouldReceive('handleDiseaseForCause')
            ->never()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);

        $hazardous = new Status($gameEquipment);
        $hazardous->setName(EquipmentStatusEnum::HAZARDOUS);

        $this->playerDiseaseService
            ->shouldReceive('handleDiseaseForCause')
            ->once()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);
    }
}
