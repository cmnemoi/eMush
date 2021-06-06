<?php

namespace Mush\Test\Disease\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\ConsumableDisease;
use Mush\Disease\Entity\ConsumableDiseaseCharacteristic;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
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

    /** @var ConsumableDiseaseServiceInterface | Mockery\Mock */
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;

    /**
     * @before
     */
    public function before()
    {
        $this->playerDiseaseService = Mockery::mock(PlayerDiseaseService::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->consumableDiseaseService = Mockery::mock(ConsumableDiseaseServiceInterface::class);

        $this->diseaseCauseService = new DiseaseCauseService(
            $this->playerDiseaseService,
            $this->randomService,
            $this->consumableDiseaseService
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

    public function testAlienFood()
    {
        $daedalus = new Daedalus();

        $player = new Player();
        $player->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment();
        $gameEquipment->setName('someName');

        $this->consumableDiseaseService
            ->shouldReceive('findConsumableDiseases')
            ->andReturn(null)
            ->once()
        ;

        $this->diseaseCauseService->handleAlienFood($player, $gameEquipment);

        $disease = new ConsumableDiseaseCharacteristic();
        $disease->setDisease('disease name');

        $consumableDisease = new ConsumableDisease();
        $consumableDisease
            ->setDiseases(new ArrayCollection([$disease]))
        ;

        $this->consumableDiseaseService
            ->shouldReceive('findConsumableDiseases')
            ->andReturn($consumableDisease)
            ->twice()
        ;

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(false)
            ->once()
        ;

        $this->diseaseCauseService->handleAlienFood($player, $gameEquipment);

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(true)
            ->once()
        ;

        $this->playerDiseaseService
            ->shouldReceive('createDiseaseFromName')
            ->once()
        ;

        $this->diseaseCauseService->handleAlienFood($player, $gameEquipment);
    }
}
