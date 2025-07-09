<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\SelfSurgery;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Disease\ConfigData\DiseaseConfigData;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Disease\Repository\InMemoryPlayerDiseaseRepository;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Disease\Service\DiseaseCauseService;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerService;
use Mush\Game\Service\Random\ProbaCollectionRandomElementService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Factory\PlayerFactory;

/**
 * @internal
 */
final class SelfSurgeryActionTest extends AbstractActionTest
{
    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    private DiseaseCauseService $diseaseCauseService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::SELF_SURGERY);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $playerDiseaseService = new PlayerDiseaseService(
            d100Roll: self::createStub(D100RollServiceInterface::class),
            eventService: self::createStub(EventServiceInterface::class),
            randomService: self::createStub(RandomServiceInterface::class),
            playerDiseaseRepository: new InMemoryPlayerDiseaseRepository()
        );
        $probaCollectionRandomElementService = new ProbaCollectionRandomElementService(
            getRandomInteger: new GetRandomIntegerService()
        );

        $this->diseaseCauseService = new DiseaseCauseService(
            consumableDiseaseService: self::createStub(ConsumableDiseaseServiceInterface::class),
            d100Roll: self::createStub(D100RollServiceInterface::class),
            probaCollectionRandomElement: $probaCollectionRandomElementService,
            playerDiseaseService: $playerDiseaseService,
        );

        $this->actionHandler = new SelfSurgery(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
            $this->diseaseCauseService
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
        $player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());
        $room = $player->getPlace();

        $gameEquipment = new GameEquipment($room);
        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$this->actionConfig]));
        $item = new EquipmentConfig();
        $item
            ->setEquipmentName(EquipmentEnum::SURGERY_PLOT)
            ->setMechanics(new ArrayCollection([$tool]));

        $gameEquipment
            ->setEquipment($item)
            ->setName(EquipmentEnum::SURGERY_PLOT);

        $diseaseConfig1 = DiseaseConfig::fromConfigData(DiseaseConfigData::getByName(InjuryEnum::BROKEN_FOOT));
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1);

        $player->addMedicalCondition($playerDisease1);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameEquipment);

        $actionVariableEvent = new ActionVariableEvent(
            $this->actionConfig,
            $this->actionProvider,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            10,
            $player,
            $this->actionHandler->getTags(),
            null
        );
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn($actionVariableEvent)
            ->once();
        $actionVariableEventCritical = new ActionVariableEvent(
            $this->actionConfig,
            $this->actionProvider,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            15,
            $player,
            $this->actionHandler->getTags(),
            null
        );
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn($actionVariableEventCritical)
            ->once();

        $this->randomService->shouldReceive('outputCriticalChances')
            ->with(10, 0, 15)
            ->andReturn(ActionOutputEnum::FAIL)
            ->once();

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Fail::class, $result);

        self::assertNotNull($player->getMedicalConditionByName(InjuryEnum::BROKEN_FOOT), 'Broken foot should not be removed');
        self::assertNotNull($player->getMedicalConditionByName(DiseaseEnum::SEPSIS), 'Player should get sepsis');
    }

    public function testExecuteSuccess()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $gameEquipment = new GameEquipment($room);
        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$this->actionConfig]));
        $item = new EquipmentConfig();
        $item
            ->setEquipmentName(EquipmentEnum::SURGERY_PLOT)
            ->setMechanics(new ArrayCollection([$tool]));

        $gameEquipment
            ->setEquipment($item)
            ->setName(EquipmentEnum::SURGERY_PLOT);

        $diseaseConfig1 = new DiseaseConfig();
        $diseaseConfig1->setType(MedicalConditionTypeEnum::DISEASE);
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setType(MedicalConditionTypeEnum::INJURY);
        $playerDisease2 = new PlayerDisease();
        $playerDisease2->setDiseaseConfig($diseaseConfig2);

        $player->addMedicalCondition($playerDisease1)->addMedicalCondition($playerDisease2);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameEquipment);

        $actionVariableEvent = new ActionVariableEvent(
            $this->actionConfig,
            $this->actionProvider,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            10,
            $player,
            $this->actionHandler->getTags(),
            null
        );
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn($actionVariableEvent)
            ->once();
        $actionVariableEventCritical = new ActionVariableEvent(
            $this->actionConfig,
            $this->actionProvider,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            15,
            $player,
            $this->actionHandler->getTags(),
            null
        );
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn($actionVariableEventCritical)
            ->once();

        $this->randomService->shouldReceive('outputCriticalChances')
            ->with(10, 0, 15)
            ->andReturn(ActionOutputEnum::SUCCESS)
            ->once();

        $this->eventService->shouldReceive('callEvent')->once();

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertNotInstanceOf(CriticalSuccess::class, $result);
    }

    public function testExecuteCriticalSuccess()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $gameEquipment = new GameEquipment($room);
        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$this->actionConfig]));
        $item = new EquipmentConfig();
        $item
            ->setEquipmentName(EquipmentEnum::SURGERY_PLOT)
            ->setMechanics(new ArrayCollection([$tool]));

        $gameEquipment
            ->setEquipment($item)
            ->setName(EquipmentEnum::SURGERY_PLOT);

        $diseaseConfig1 = new DiseaseConfig();
        $diseaseConfig1->setType(MedicalConditionTypeEnum::DISEASE);
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setType(MedicalConditionTypeEnum::INJURY);
        $playerDisease2 = new PlayerDisease();
        $playerDisease2->setDiseaseConfig($diseaseConfig2);

        $player->addMedicalCondition($playerDisease1)->addMedicalCondition($playerDisease2);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameEquipment);
        $actionVariableEvent = new ActionVariableEvent(
            $this->actionConfig,
            $this->actionProvider,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            10,
            $player,
            $this->actionHandler->getTags(),
            null
        );
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn($actionVariableEvent)
            ->once();
        $actionVariableEventCritical = new ActionVariableEvent(
            $this->actionConfig,
            $this->actionProvider,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            15,
            $player,
            $this->actionHandler->getTags(),
            null
        );
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn($actionVariableEventCritical)
            ->once();

        $this->randomService->shouldReceive('outputCriticalChances')
            ->with(10, 0, 15)
            ->andReturn(ActionOutputEnum::CRITICAL_SUCCESS)
            ->once();

        $this->eventService->shouldReceive('callEvent')->once();

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(CriticalSuccess::class, $result);
    }
}
