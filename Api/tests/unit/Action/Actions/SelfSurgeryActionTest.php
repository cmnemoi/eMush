<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\CriticalSuccess;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\SelfSurgery;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\TypeEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;

class SelfSurgeryActionTest extends AbstractActionTest
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

        $this->actionEntity = $this->createActionEntity(ActionEnum::SELF_HEAL);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->modifierService = Mockery::mock(ModifierServiceInterface::class);

        $this->action = new SelfSurgery(
            $this->eventDispatcher,
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
        Mockery::close();
    }

    public function testExecuteFail()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $diseaseConfig1 = new DiseaseConfig();
        $diseaseConfig1->setType(TypeEnum::DISEASE);
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setType(TypeEnum::INJURY);
        $playerDisease2 = new PlayerDisease();
        $playerDisease2->setDiseaseConfig($diseaseConfig2);

        $player->addMedicalCondition($playerDisease1)->addMedicalCondition($playerDisease2);

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::PERCENTAGE, 10, ActionEnum::SELF_SURGERY, Mockery::any())
            ->once()
            ->andReturn(10)
        ;

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::CRITICAL_PERCENTAGE, 5, ActionEnum::SELF_SURGERY, Mockery::any())
            ->once()
            ->andReturn(15)
        ;

        $this->randomService->shouldReceive('outputCriticalChances')
            ->with(10, 0, 15)
            ->andReturn(ActionOutputEnum::FAIL)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch');
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteSuccess()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $diseaseConfig1 = new DiseaseConfig();
        $diseaseConfig1->setType(TypeEnum::DISEASE);
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setType(TypeEnum::INJURY);
        $playerDisease2 = new PlayerDisease();
        $playerDisease2->setDiseaseConfig($diseaseConfig2);

        $player->addMedicalCondition($playerDisease1)->addMedicalCondition($playerDisease2);

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::PERCENTAGE, 10, ActionEnum::SELF_SURGERY, Mockery::any())
            ->once()
            ->andReturn(10)
        ;

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::CRITICAL_PERCENTAGE, 5, ActionEnum::SELF_SURGERY, Mockery::any())
            ->once()
            ->andReturn(15)
        ;

        $this->randomService->shouldReceive('outputCriticalChances')
            ->with(10, 0, 15)
            ->andReturn(ActionOutputEnum::SUCCESS)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomDisease')
            ->withArgs(fn (PlayerDiseaseCollection $injuries) => $injuries->count() === 1)
            ->andReturn($playerDisease2)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertNotInstanceOf(CriticalSuccess::class, $result);
    }

    public function testExecuteCriticalSuccess()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $diseaseConfig1 = new DiseaseConfig();
        $diseaseConfig1->setType(TypeEnum::DISEASE);
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setType(TypeEnum::INJURY);
        $playerDisease2 = new PlayerDisease();
        $playerDisease2->setDiseaseConfig($diseaseConfig2);

        $player->addMedicalCondition($playerDisease1)->addMedicalCondition($playerDisease2);

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::PERCENTAGE, 10, ActionEnum::SELF_SURGERY, Mockery::any())
            ->once()
            ->andReturn(10)
        ;

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ActionEnum::SURGERY], ModifierTargetEnum::CRITICAL_PERCENTAGE, 5, ActionEnum::SELF_SURGERY, Mockery::any())
            ->once()
            ->andReturn(15)
        ;

        $this->randomService->shouldReceive('outputCriticalChances')
            ->with(10, 0, 15)
            ->andReturn(ActionOutputEnum::CRITICAL_SUCCESS)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomDisease')
            ->withArgs(fn (PlayerDiseaseCollection $injuries) => $injuries->count() === 1)
            ->andReturn($playerDisease2)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch');
        $result = $this->action->execute();

        $this->assertInstanceOf(CriticalSuccess::class, $result);
    }
}
