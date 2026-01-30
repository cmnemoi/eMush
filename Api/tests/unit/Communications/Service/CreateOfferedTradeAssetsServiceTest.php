<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Service\CreateOfferedTradeAssetsService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Repository\InMemoryDaedalusRepository;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Equipment\Service\DamageEquipmentServiceInterface;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\EquipmentService;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\Repository\InMemoryProjectRepository;
use Mush\Project\Service\FinishProjectService;
use Mush\Project\Service\FinishRandomDaedalusProjectsService;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Repository\InMemoryRoomLogRepository;
use Mush\RoomLog\Service\RoomLogService;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryTradeOptionRepository;
use Mush\Tests\unit\Communications\TestDoubles\Service\FakeEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CreateOfferedTradeAssetsServiceTest extends TestCase
{
    private CreateOfferedTradeAssetsService $createOfferedTradeAssets;

    private FinishProjectService $finishProject;
    private FinishRandomDaedalusProjectsService $finishRandomDaedalusProjects;
    private DamageEquipmentServiceInterface $damageEquipmentService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private InMemoryDaedalusRepository $daedalusRepository;
    private InMemoryTradeOptionRepository $tradeOptionRepository;
    private InMemoryProjectRepository $projectRepository;
    private InMemoryRoomLogRepository $roomLogRepository;
    private RoomLogService $roomLogService;
    private EventServiceInterface|MockInterface $eventServiceSpy;

    protected function setUp(): void
    {
        $this->daedalusRepository = new InMemoryDaedalusRepository();
        $this->projectRepository = new InMemoryProjectRepository();
        $this->roomLogRepository = new InMemoryRoomLogRepository();
        $this->roomLogService = new RoomLogService(
            cycleService: self::createStub(CycleServiceInterface::class),
            d100Roll: new FakeD100RollService(),
            getRandomInteger: new FakeGetRandomIntegerService(0),
            roomLogRepository: $this->roomLogRepository,
            translationService: self::createStub(TranslationServiceInterface::class),
        );
        $this->eventServiceSpy = \Mockery::spy(EventServiceInterface::class);

        $this->finishRandomDaedalusProjects = new FinishRandomDaedalusProjectsService(
            daedalusRepository: $this->daedalusRepository,
            eventService: new FakeEventService(),
            getRandomElementsFromArray: new FakeGetRandomElementsFromArrayService(),
            projectRepository: $this->projectRepository
        );

        $this->finishProject = new FinishProjectService(
            eventService: new FakeEventService(),
            projectRepository: $this->projectRepository
        );

        $this->tradeOptionRepository = new InMemoryTradeOptionRepository();

        $this->gameEquipmentService = new GameEquipmentService(
            entityManager: self::createStub(EntityManagerInterface::class),
            repository: self::createStub(GameEquipmentRepositoryInterface::class),
            damageEquipmentService: self::createStub(DamageEquipmentServiceInterface::class),
            equipmentService: new EquipmentService(),
            randomService: self::createStub(RandomServiceInterface::class),
            eventService: self::createStub(EventServiceInterface::class),
            statusService: self::createStub(StatusServiceInterface::class),
            equipmentEffectService: self::createStub(EquipmentEffectServiceInterface::class),
        );

        $this->createOfferedTradeAssets = new CreateOfferedTradeAssetsService(
            eventService: $this->eventServiceSpy,
            finishProject: $this->finishProject,
            finishRandomDaedalusProjects: $this->finishRandomDaedalusProjects,
            gameEquipmentService: $this->gameEquipmentService,
            roomLogService: $this->roomLogService,
            tradeOptionRepository: $this->tradeOptionRepository
        );
    }

    public function testShouldCreateOfferedTradeItemAssetsInPlayerRoom(): void
    {
        $player = $this->givenPlayerInDaedalus();
        $tradeOption = $this->givenTradeOptionOfferingItems(ItemEnum::HYDROPOT, 2);

        $this->whenICreateOfferedTradeAssets($player, $tradeOption);

        $this->thenPlayerRoomShouldHaveItems($player, ItemEnum::HYDROPOT, 2);
    }

    public function testShouldFinishRandomNeronProjects(): void
    {
        $player = $this->givenPlayerInDaedalus();
        $tradeOption = $this->givenTradeOptionOfferingRandomProjects(quantity: 2);
        $this->givenDaedalusHasUnfinishedProjects($player->getDaedalus(), 3);

        $this->whenICreateOfferedTradeAssets($player, $tradeOption);

        $this->thenDaedalusShouldHaveFinishedProjects($player->getDaedalus(), 2);
    }

    public function testShouldFinishSpecificNeronProject(): void
    {
        $player = $this->givenPlayerInDaedalus();
        $tradeOption = $this->givenTradeOptionOfferingSpecificProject(ProjectName::PILGRED);
        $pilgredProject = $this->givenDaedalusHasPilgredProject($player->getDaedalus());

        $this->whenICreateOfferedTradeAssets($player, $tradeOption);

        $this->thenProjectShouldBeFinished($pilgredProject);
    }

    public function testShouldCreateOxygenCapsulesInPlayerRoom(): void
    {
        $player = $this->givenPlayerInDaedalus();
        $tradeOption = $this->givenTradeOptionOfferingDaedalusVariable(DaedalusVariableEnum::OXYGEN, 10);

        $this->whenICreateOfferedTradeAssets($player, $tradeOption);

        $this->thenPlayerRoomShouldHaveItems($player, ItemEnum::OXYGEN_CAPSULE, 10);
    }

    public function testShouldCreateFuelCapsulesInPlayerRoom(): void
    {
        $player = $this->givenPlayerInDaedalus();
        $tradeOption = $this->givenTradeOptionOfferingDaedalusVariable(DaedalusVariableEnum::FUEL, 10);

        $this->whenICreateOfferedTradeAssets($player, $tradeOption);

        $this->thenPlayerRoomShouldHaveItems($player, ItemEnum::FUEL_CAPSULE, 10);
    }

    public function testShouldCreateRoomLogForCreatedItems(): void
    {
        $player = $this->givenPlayerInDaedalus();
        $tradeOption = $this->givenTradeOptionOfferingItems(ItemEnum::HYDROPOT, 2);

        $this->whenICreateOfferedTradeAssets($player, $tradeOption);

        $this->thenRoomLogShouldBeCreated(LogEnum::TRADE_ASSETS_CREATED);
    }

    public function testShouldCreateRoomLogForCreatedDaedalusVariableItems(): void
    {
        $player = $this->givenPlayerInDaedalus();
        $tradeOption = $this->givenTradeOptionOfferingDaedalusVariable(DaedalusVariableEnum::OXYGEN, 10);

        $this->whenICreateOfferedTradeAssets($player, $tradeOption);

        $this->thenRoomLogShouldBeCreated(LogEnum::TRADE_ASSETS_CREATED);
    }

    public function testShouldCreateTradeAssetsCreatedEvent(): void
    {
        $player = $this->givenPlayerInDaedalus();
        $tradeOption = $this->givenTradeOptionOfferingItems(ItemEnum::HYDROPOT, 2);

        $this->whenICreateOfferedTradeAssets($player, $tradeOption);

        $this->eventServiceSpy->shouldHaveReceived('callEvent')->once();
    }

    private function givenPlayerInDaedalus(): Player
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $this->daedalusRepository->save($daedalus);

        return PlayerFactory::createPlayerWithDaedalus($daedalus);
    }

    private function givenTradeOptionOfferingItems(string $itemName, int $quantity): TradeOption
    {
        $tradeOption = new TradeOption(
            requiredAssets: [],
            offeredAssets: [
                new TradeAsset(
                    type: TradeAssetEnum::ITEM,
                    quantity: $quantity,
                    assetName: $itemName,
                ),
            ],
        );
        $this->tradeOptionRepository->save($tradeOption);

        return $tradeOption;
    }

    private function givenTradeOptionOfferingRandomProjects(int $quantity): TradeOption
    {
        $tradeOption = new TradeOption(
            requiredAssets: [],
            offeredAssets: [new TradeAsset(type: TradeAssetEnum::RANDOM_PROJECT, quantity: $quantity)],
        );
        $this->tradeOptionRepository->save($tradeOption);

        return $tradeOption;
    }

    private function givenTradeOptionOfferingSpecificProject(ProjectName $projectName): TradeOption
    {
        $tradeOption = new TradeOption(
            requiredAssets: [],
            offeredAssets: [new TradeAsset(type: TradeAssetEnum::SPECIFIC_PROJECT, quantity: 1, assetName: $projectName->toString())],
        );
        $this->tradeOptionRepository->save($tradeOption);

        return $tradeOption;
    }

    private function givenTradeOptionOfferingDaedalusVariable(string $variable, int $quantity): TradeOption
    {
        $tradeOption = new TradeOption(
            requiredAssets: [],
            offeredAssets: [new TradeAsset(type: TradeAssetEnum::DAEDALUS_VARIABLE, quantity: $quantity, assetName: $variable)],
        );
        $this->tradeOptionRepository->save($tradeOption);

        return $tradeOption;
    }

    private function givenDaedalusHasUnfinishedProjects(Daedalus $daedalus, int $count): void
    {
        for ($i = 0; $i < $count; ++$i) {
            ProjectFactory::createDummyNeronProjectForDaedalus($daedalus);
        }
    }

    private function givenDaedalusHasPilgredProject(Daedalus $daedalus): Project
    {
        return ProjectFactory::createPilgredProjectForDaedalus($daedalus);
    }

    private function whenICreateOfferedTradeAssets(Player $player, TradeOption $tradeOption): void
    {
        $this->createOfferedTradeAssets->execute($player, $tradeOption->getId());
    }

    private function thenPlayerRoomShouldHaveItems(Player $player, string $itemName, int $expectedCount): void
    {
        self::assertCount(
            $expectedCount,
            $player->getPlace()->getAllEquipmentsByName($itemName),
            \sprintf('Player room should have %d %s', $expectedCount, $itemName)
        );
    }

    private function thenDaedalusShouldHaveFinishedProjects(Daedalus $daedalus, int $expectedCount): void
    {
        self::assertCount(
            $expectedCount,
            $daedalus->getFinishedNeronProjects(),
            \sprintf('Daedalus should have %d finished projects', $expectedCount)
        );
    }

    private function thenProjectShouldBeFinished(Project $project): void
    {
        self::assertTrue(
            $project->isFinished(),
            \sprintf('%s project should be finished', $project->getName())
        );
    }

    private function thenRoomLogShouldBeCreated(string $logKey): void
    {
        self::assertNotNull(
            $this->roomLogRepository->findOneByLogKey($logKey),
            \sprintf('Room log should be created for %s', $logKey)
        );
    }
}
