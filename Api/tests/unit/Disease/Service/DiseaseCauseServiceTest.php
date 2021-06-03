<?php

namespace Mush\Test\Disease\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Service\DiseaseCauseService;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;

class DiseaseCauseServiceTest extends TestCase
{
    private DiseaseCauseService $diseaseCauseService;

    /** @var PlayerDiseaseService | Mockery\Mock */
    private PlayerDiseaseService $playerDiseaseService;

    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        $this->playerDiseaseService = Mockery::mock(PlayerDiseaseService::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->diseaseCauseService = new DiseaseCauseService(
            $this->playerDiseaseService,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testSpoiledFoodHazardous()
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

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(false)
            ->once()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(true)
            ->once()
        ;

        $this->playerDiseaseService
            ->shouldReceive('handleDiseaseForCause')
            ->once()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);
    }

    public function testSpoiledFoodDecomposing()
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
        $hazardous->setName(EquipmentStatusEnum::DECOMPOSING);

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(false)
            ->once()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(true)
            ->once()
        ;

        $this->playerDiseaseService
            ->shouldReceive('handleDiseaseForCause')
            ->once()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);
    }
}
