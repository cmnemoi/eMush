<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\CriticalSuccess;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Surgery;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\TypeEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;

class SurgeryActionTest extends AbstractActionTest
{
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /** @var ModifierServiceInterface|Mockery\Mock */
    private ModifierServiceInterface $modifierService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::SURGERY);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->modifierService = \Mockery::mock(ModifierServiceInterface::class);

        $this->action = new Surgery(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
            $this->modifierService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecuteFail()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $playerToHeal = $this->createPlayer(new Daedalus(), $room);

        $diseaseConfig1 = new DiseaseConfig();
        $diseaseConfig1->setType(TypeEnum::DISEASE);
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1)->setPlayer($playerToHeal);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setType(TypeEnum::INJURY);
        $playerDisease2 = new PlayerDisease();
        $playerDisease2->setDiseaseConfig($diseaseConfig2)->setPlayer($playerToHeal);

        $playerToHeal->addMedicalCondition($playerDisease1)->addMedicalCondition($playerDisease2);

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::PERCENTAGE, 10, [ActionEnum::SURGERY], \Mockery::any())
            ->once()
            ->andReturn(10)
        ;

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::CRITICAL_PERCENTAGE, 15, [ActionEnum::SURGERY], \Mockery::any())
            ->once()
            ->andReturn(15)
        ;

        $this->randomService->shouldReceive('outputCriticalChances')
            ->with(10, 0, 15)
            ->andReturn(ActionOutputEnum::FAIL)
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')->once();

        $this->action->loadParameters($this->actionEntity, $player, $playerToHeal);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteSuccess()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $playerToHeal = $this->createPlayer(new Daedalus(), $room);

        $diseaseConfig1 = new DiseaseConfig();
        $diseaseConfig1->setType(TypeEnum::DISEASE);
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setType(TypeEnum::INJURY);
        $playerDisease2 = new PlayerDisease();
        $playerDisease2->setDiseaseConfig($diseaseConfig2);

        $playerToHeal->addMedicalCondition($playerDisease1)->addMedicalCondition($playerDisease2);

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::PERCENTAGE, 10, [ActionEnum::SURGERY], \Mockery::any())
            ->once()
            ->andReturn(10)
        ;

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::CRITICAL_PERCENTAGE, 15, [ActionEnum::SURGERY], \Mockery::any())
            ->once()
            ->andReturn(15)
        ;

        $this->randomService->shouldReceive('outputCriticalChances')
            ->with(10, 0, 15)
            ->andReturn(ActionOutputEnum::SUCCESS)
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')->once();

        $this->action->loadParameters($this->actionEntity, $player, $playerToHeal);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertNotInstanceOf(CriticalSuccess::class, $result);
    }

    public function testExecuteCriticalSuccess()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $playerToHeal = $this->createPlayer(new Daedalus(), $room);

        $diseaseConfig1 = new DiseaseConfig();
        $diseaseConfig1->setType(TypeEnum::DISEASE);
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setType(TypeEnum::INJURY);
        $playerDisease2 = new PlayerDisease();
        $playerDisease2->setDiseaseConfig($diseaseConfig2);

        $playerToHeal->addMedicalCondition($playerDisease1)->addMedicalCondition($playerDisease2);

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::PERCENTAGE, 10, [ActionEnum::SURGERY], \Mockery::any())
            ->once()
            ->andReturn(10)
        ;

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::CRITICAL_PERCENTAGE, 15, [ActionEnum::SURGERY], \Mockery::any())
            ->once()
            ->andReturn(15)
        ;

        $this->randomService->shouldReceive('outputCriticalChances')
            ->with(10, 0, 15)
            ->andReturn(ActionOutputEnum::CRITICAL_SUCCESS)
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')->once();

        $this->action->loadParameters($this->actionEntity, $player, $playerToHeal);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $result = $this->action->execute();

        $this->assertInstanceOf(CriticalSuccess::class, $result);
    }
}
