<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\Service;

use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Service\ConsumeRequiredTradeAssetsService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Exception\GameException;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\ClosedPlayerRepositoryInterface;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Player\Service\PlayerService;
use Mush\Project\Entity\Project;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\Repository\ProjectRepositoryInterface;
use Mush\Project\Service\DeactivateProjectService;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryTradeOptionRepository;
use Mush\Tests\unit\Communications\TestDoubles\Service\FakeDeleteEquipmentService;
use Mush\Tests\unit\Communications\TestDoubles\Service\FakeEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ConsumeRequiredTradeAssetsServiceTest extends TestCase
{
    private ConsumeRequiredTradeAssetsService $consumeRequiredTradeAssets;
    private InMemoryTradeOptionRepository $tradeOptionRepository;
    private DeactivateProjectService $deactivateProject;
    private FakeDeleteEquipmentService $deleteEquipmentService;
    private FakeEventService $eventService;
    private FakeGetRandomElementsFromArrayService $getRandomElementsFromArray;
    private PlayerService $playerService;
    private Daedalus $daedalus;
    private Player $trader;

    protected function setUp(): void
    {
        $this->initializeServices();
        $this->initializeTradeOptionRepository();
        $this->initializeConsumeRequiredTradeAssetsService();
    }

    /**
     * @dataProvider provideShouldRemoveExpectedAmountOfDaedalusVariableCases
     */
    public function testShouldRemoveExpectedAmountOfDaedalusVariable(string $variable, int $quantity): void
    {
        // Given
        $this->givenDaedalusAndTrader();
        $tradeOption = $this->givenTradeOptionRequiringDaedalusVariable($variable, $quantity);
        $this->givenDaedalusHasVariable($variable, $quantity + 5);

        // When
        $this->whenConsumingTradeAssets($tradeOption);

        // Then
        $this->thenDaedalusShouldHaveVariable($variable, 5);
    }

    public static function provideShouldRemoveExpectedAmountOfDaedalusVariableCases(): iterable
    {
        return [
            '10 oxygen' => [DaedalusVariableEnum::OXYGEN, 10],
            '10 fuel' => [DaedalusVariableEnum::FUEL, 10],
        ];
    }

    public function testShouldThrowIfNotEnoughDaedalusVariable(): void
    {
        // Given
        $this->givenDaedalusAndTrader();
        $tradeOption = $this->givenTradeOptionRequiringDaedalusVariable(DaedalusVariableEnum::OXYGEN, 10);
        $this->givenDaedalusHasVariable(DaedalusVariableEnum::OXYGEN, 5);

        // Then
        $this->thenItShouldThrowGameException($tradeOption);
    }

    public function testShouldDeleteExpectedAmountOfEquipmentInStorage(): void
    {
        // Given
        $this->givenDaedalusWithStoragesAndTrader();
        $tradeOption = $this->givenTradeOptionRequiringItems(ItemEnum::HYDROPOT, 2);
        $this->givenItemsInStorages(ItemEnum::HYDROPOT, [
            RoomEnum::FRONT_STORAGE => 1,
            RoomEnum::CENTER_BRAVO_STORAGE => 1,
            RoomEnum::CENTER_ALPHA_STORAGE => 1,
        ]);

        // When
        $this->whenConsumingTradeAssets($tradeOption);

        // Then
        $this->thenStorageShouldHaveItems(RoomEnum::FRONT_STORAGE, ItemEnum::HYDROPOT, 0);
        $this->thenStorageShouldHaveItems(RoomEnum::CENTER_ALPHA_STORAGE, ItemEnum::HYDROPOT, 0);
        $this->thenStorageShouldHaveItems(RoomEnum::CENTER_BRAVO_STORAGE, ItemEnum::HYDROPOT, 1);
    }

    public function testShouldThrowIfNotEnoughItemsInDaedalusStorages(): void
    {
        // Given
        $this->givenDaedalusWithStoragesAndTrader();
        $tradeOption = $this->givenTradeOptionRequiringItems(ItemEnum::HYDROPOT, 2);
        $this->givenItemsInStorages(ItemEnum::HYDROPOT, [
            RoomEnum::FRONT_STORAGE => 1,
        ]);

        // Then
        $this->thenItShouldThrowGameException($tradeOption);
    }

    public function testShouldKillExpectedAmountOfRandomPlayersInStorages(): void
    {
        // Given
        $this->givenDaedalusWithStoragesAndTrader();
        $tradeOption = $this->givenTradeOptionRequiringRandomPlayers(2);
        $chun = $this->givenHighlyInactivePlayer(CharacterEnum::CHUN);
        $derek = $this->givenInactivePlayerInStorage(CharacterEnum::DEREK, RoomEnum::CENTER_ALPHA_STORAGE);
        $paola = $this->givenInactivePlayerInStorage(CharacterEnum::PAOLA, RoomEnum::CENTER_BRAVO_STORAGE);

        // When
        $this->whenConsumingTradeAssets($tradeOption);

        // Then
        $this->thenPlayerShouldBeDead($chun);
        $this->thenPlayerShouldBeDead($derek);
        $this->thenPlayerShouldBeAlive($paola);
    }

    public function testShouldThrowIfNotEnoughTradablePlayersInDaedalus(): void
    {
        // Given
        $this->givenDaedalusWithStoragesAndTrader();
        $tradeOption = $this->givenTradeOptionRequiringRandomPlayers(2);
        $this->givenPlayerInStorage(RoomEnum::CENTER_ALPHA_STORAGE);

        // Then
        $this->thenItShouldThrowGameException($tradeOption);
    }

    public function testShouldKillSpecifiedPlayer(): void
    {
        // Given
        $this->givenDaedalusWithStoragesAndTrader();
        $tradeOption = $this->givenTradeOptionRequiringSpecificPlayer(CharacterEnum::CHUN);
        $chun = $this->givenInactivePlayerInStorage(CharacterEnum::CHUN, RoomEnum::CENTER_ALPHA_STORAGE);

        // When
        $this->whenConsumingTradeAssets($tradeOption);

        // Then
        $this->thenPlayerShouldBeDead($chun);
    }

    public function testShouldThrowIfSpecificPlayerIsNotTradable(): void
    {
        // Given
        $this->givenDaedalusWithStoragesAndTrader();
        $tradeOption = $this->givenTradeOptionRequiringSpecificPlayer(CharacterEnum::CHUN);
        $this->givenPlayerInStorage(RoomEnum::CENTER_ALPHA_STORAGE, CharacterEnum::CHUN);

        // Then
        $this->thenItShouldThrowGameException($tradeOption);
    }

    public function testShouldDeactivateRandomProjects(): void
    {
        // Given
        $this->givenDaedalusAndTrader();
        $project = $this->givenFinishedProject();
        $tradeOption = $this->givenTradeOptionRequiringRandomProject();

        // When
        $this->whenConsumingTradeAssets($tradeOption);

        // Then
        $this->thenProjectShouldBeDeactivated($project);
    }

    public function testShouldThrowIfNotEnoughFinishedProjectsToTrade(): void
    {
        // Given
        $this->givenDaedalusAndTrader();
        $tradeOption = $this->givenTradeOptionRequiringRandomProject();

        // Then
        $this->thenItShouldThrowGameException($tradeOption);
    }

    private function givenDaedalusAndTrader(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->trader = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
    }

    private function givenDaedalusWithStoragesAndTrader(): void
    {
        $this->givenDaedalusAndTrader();
        foreach (RoomEnum::getStorages() as $storage) {
            Place::createRoomByNameInDaedalus($storage, $this->daedalus);
        }
    }

    private function givenTradeOptionRequiringDaedalusVariable(string $variable, int $quantity): TradeOption
    {
        $tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(
                    type: TradeAssetEnum::DAEDALUS_VARIABLE,
                    quantity: $quantity,
                    assetName: $variable,
                ),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($tradeOption);

        return $tradeOption;
    }

    private function givenTradeOptionRequiringItems(string $itemName, int $quantity): TradeOption
    {
        $tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(
                    type: TradeAssetEnum::ITEM,
                    quantity: $quantity,
                    assetName: $itemName,
                ),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($tradeOption);

        return $tradeOption;
    }

    private function givenTradeOptionRequiringRandomPlayers(int $quantity): TradeOption
    {
        $tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(type: TradeAssetEnum::RANDOM_PLAYER, quantity: $quantity),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($tradeOption);

        return $tradeOption;
    }

    private function givenTradeOptionRequiringSpecificPlayer(string $characterName): TradeOption
    {
        $tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(type: TradeAssetEnum::SPECIFIC_PLAYER, quantity: 1, assetName: $characterName),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($tradeOption);

        return $tradeOption;
    }

    private function givenTradeOptionRequiringRandomProject(): TradeOption
    {
        $tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(type: TradeAssetEnum::RANDOM_PROJECT, quantity: 1),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($tradeOption);

        return $tradeOption;
    }

    private function givenDaedalusHasVariable(string $variable, int $value): void
    {
        $this->daedalus->setVariableValueByName($value, $variable);
    }

    private function givenItemsInStorages(string $itemName, array $storageQuantities): void
    {
        foreach ($storageQuantities as $storageName => $quantity) {
            for ($i = 0; $i < $quantity; ++$i) {
                GameEquipmentFactory::createItemByNameForHolder(
                    $itemName,
                    $this->daedalus->getPlaceByName($storageName),
                );
            }
        }
    }

    private function givenHighlyInactivePlayer(string $characterName): Player
    {
        $player = PlayerFactory::createPlayerByNameAndDaedalus($characterName, $this->daedalus);
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::HIGHLY_INACTIVE,
            holder: $player,
        );

        return $player;
    }

    private function givenInactivePlayerInStorage(string $characterName, string $storageName): Player
    {
        $player = PlayerFactory::createPlayerByNameAndPlace($characterName, $this->daedalus->getPlaceByName($storageName));
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::INACTIVE,
            holder: $player,
        );

        return $player;
    }

    private function givenPlayerInStorage(string $storageName, ?string $characterName = null): Player
    {
        if ($characterName !== null) {
            return PlayerFactory::createPlayerByNameAndPlace($characterName, $this->daedalus->getPlaceByNameOrThrow($storageName));
        }

        return PlayerFactory::createPlayerInPlace($this->daedalus->getPlaceByName($storageName));
    }

    private function givenFinishedProject(): Project
    {
        $project = ProjectFactory::createDummyNeronProjectForDaedalus($this->daedalus);
        $project->finish();

        return $project;
    }

    private function whenConsumingTradeAssets(TradeOption $tradeOption): void
    {
        $this->consumeRequiredTradeAssets->execute($this->trader, $tradeOption->getId());
    }

    private function thenDaedalusShouldHaveVariable(string $variable, int $expectedValue): void
    {
        self::assertEquals($expectedValue, $this->daedalus->getVariableValueByName($variable));
    }

    private function thenStorageShouldHaveItems(string $storageName, string $itemName, int $expectedCount): void
    {
        self::assertEquals($expectedCount, $this->daedalus->getPlaceByName($storageName)->getAllEquipmentsByName($itemName)->count());
    }

    private function thenPlayerShouldBeDead(Player $player): void
    {
        self::assertTrue($player->isDead());
    }

    private function thenPlayerShouldBeAlive(Player $player): void
    {
        self::assertTrue($player->isAlive());
    }

    private function thenProjectShouldBeDeactivated(Project $project): void
    {
        self::assertFalse($project->isFinished());
    }

    private function thenItShouldThrowGameException(TradeOption $tradeOption): void
    {
        $this->expectException(GameException::class);
        $this->whenConsumingTradeAssets($tradeOption);
    }

    // Setup methods
    private function initializeServices(): void
    {
        $this->deactivateProject = new DeactivateProjectService(
            $this->createModifierCreationServiceStub(),
            $this->createProjectRepositoryStub(),
        );
        $this->deleteEquipmentService = new FakeDeleteEquipmentService();
        $this->eventService = new FakeEventService();
        $this->getRandomElementsFromArray = new FakeGetRandomElementsFromArrayService();
        $this->playerService = new PlayerService(
            $this->createClosedPlayerRepositoryStub(),
            $this->createDaedalusRepositoryStub(),
            $this->eventService,
            $this->createPlayerRepositoryStub(),
            $this->createRoomLogServiceStub(),
            $this->createPlayerInfoRepositoryStub(),
        );
    }

    private function initializeTradeOptionRepository(): void
    {
        $this->tradeOptionRepository = new InMemoryTradeOptionRepository();
    }

    private function initializeConsumeRequiredTradeAssetsService(): void
    {
        $this->consumeRequiredTradeAssets = new ConsumeRequiredTradeAssetsService(
            $this->deactivateProject,
            $this->deleteEquipmentService,
            $this->eventService,
            $this->getRandomElementsFromArray,
            $this->playerService,
            $this->tradeOptionRepository
        );
    }

    private function createModifierCreationServiceStub(): ModifierCreationServiceInterface
    {
        // @var ModifierCreationServiceInterface
        return self::createStub(ModifierCreationServiceInterface::class);
    }

    private function createProjectRepositoryStub(): ProjectRepositoryInterface
    {
        // @var ProjectRepositoryInterface
        return self::createStub(ProjectRepositoryInterface::class);
    }

    private function createClosedPlayerRepositoryStub(): ClosedPlayerRepositoryInterface
    {
        // @var ClosedPlayerRepositoryInterface
        return self::createStub(ClosedPlayerRepositoryInterface::class);
    }

    private function createDaedalusRepositoryStub(): DaedalusRepositoryInterface
    {
        // @var DaedalusRepositoryInterface
        return self::createStub(DaedalusRepositoryInterface::class);
    }

    private function createPlayerRepositoryStub(): PlayerRepositoryInterface
    {
        // @var PlayerRepositoryInterface
        return self::createStub(PlayerRepositoryInterface::class);
    }

    private function createRoomLogServiceStub(): RoomLogServiceInterface
    {
        // @var RoomLogServiceInterface
        return self::createStub(RoomLogServiceInterface::class);
    }

    private function createPlayerInfoRepositoryStub(): PlayerInfoRepositoryInterface
    {
        // @var PlayerInfoRepositoryInterface
        return self::createStub(PlayerInfoRepositoryInterface::class);
    }
}
