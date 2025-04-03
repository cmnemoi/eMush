<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\Service;

use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Service\AreTradeOptionConditionsAreMetService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Factory\ProjectFactory;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryTradeOptionRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AreTradeOptionConditionsAreMetServiceTest extends TestCase
{
    private AreTradeOptionConditionsAreMetService $areTradeOptionConditionsAreMet;
    private InMemoryTradeOptionRepository $tradeOptionRepository;
    private Daedalus $daedalus;
    private Player $trader;
    private TradeOption $tradeOption;

    protected function setUp(): void
    {
        $this->tradeOptionRepository = new InMemoryTradeOptionRepository();
        $this->areTradeOptionConditionsAreMet = new AreTradeOptionConditionsAreMetService($this->tradeOptionRepository);
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->trader = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
    }

    public function testShouldReturnFalseIfNoPlayerInRoomHasTradeOptionRequiredSkill(): void
    {
        $this->givenTradeOptionRequiringSkill(SkillEnum::DIPLOMAT);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldNotBeExecutable($canExecuteTrade, 'Trade option should not be valid if no player in room has the required skill');
    }

    public function testShouldReturnTrueIfPlayerInRoomHasTradeOptionRequiredSkill(): void
    {
        $this->givenTradeOptionRequiringSkill(SkillEnum::DIPLOMAT);
        $this->givenPlayerInTraderRoomWithSkill(SkillEnum::DIPLOMAT);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if player in room has the required skill');
    }

    public function testPolyvalentShouldUnlockDiplomatTradeOption(): void
    {
        $this->givenTradeOptionRequiringSkill(SkillEnum::DIPLOMAT);
        $this->givenPlayerInTraderRoomWithSkill(SkillEnum::POLYVALENT);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Diplomat trade option should be valid if polyvalent player is in room');
    }

    public function testPolyvalentShouldUnlockBotanistTradeOption(): void
    {
        $this->givenTradeOptionRequiringSkill(SkillEnum::BOTANIST);
        $this->givenPlayerInTraderRoomWithSkill(SkillEnum::POLYVALENT);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Botanist trade option should be valid if polyvalent player is in room');
    }

    public function testShouldReturnTrueIfTradeDoesNotRequireSkill(): void
    {
        $this->givenTradeOptionWithoutRequirements();

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if it does not require a skill');
    }

    public function testShouldReturnFalseIfRequiredItemAssetsAreNotInAStorage(): void
    {
        $this->givenStorageRoomsInDaedalus();
        $this->givenTradeOptionRequiringItem(ItemEnum::HYDROPOT, 1);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldNotBeExecutable($canExecuteTrade, 'Trade option should not be valid if required item assets are not in a storage');
    }

    public function testShouldReturnTrueIfRequiredItemAssetsAreInAStorage(): void
    {
        $this->givenStorageRoomsInDaedalus();
        $this->givenTradeOptionRequiringItem(ItemEnum::HYDROPOT, 2);
        $this->givenItemsInStorage(ItemEnum::HYDROPOT, [RoomEnum::FRONT_STORAGE, RoomEnum::CENTER_BRAVO_STORAGE]);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if required item assets are in a storage');
    }

    public function testShouldReturnFalseIfRequiredSkillIsInTheRoomButRequiredItemAssetsAreNotInAStorage(): void
    {
        $this->givenStorageRoomsInDaedalus();
        $this->givenTradeOptionRequiringSkillAndItem(SkillEnum::DIPLOMAT, ItemEnum::HYDROPOT, 1);
        $this->givenPlayerInTraderRoomWithSkill(SkillEnum::DIPLOMAT);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldNotBeExecutable($canExecuteTrade, 'Trade option should not be valid if required skill is in the room but required item assets are not in a storage');
    }

    public function testShouldReturnTrueIfRequiredRandomPlayerAssetsAreHighlyInactive(): void
    {
        $this->givenStorageRoomsInDaedalus();
        $this->givenTradeOptionRequiringRandomPlayer(1);
        $this->givenHighlyInactivePlayerInDaedalus();

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if required random player assets are highly inactive');
    }

    public function testShouldReturnFalseIfRequiredRandomPlayerAssetsAreNotHighlyInactive(): void
    {
        $this->givenStorageRoomsInDaedalus();
        $this->givenTradeOptionRequiringRandomPlayer(1);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldNotBeExecutable($canExecuteTrade, 'Trade option should not be valid if required random player assets are not highly inactive');
    }

    public function testShouldReturnTrueIfRequiredRandomPlayerAssetsAreInactiveInAStorage(): void
    {
        $this->givenStorageRoomsInDaedalus();
        $this->givenTradeOptionRequiringRandomPlayer(1);
        $this->givenInactivePlayerInStorage(RoomEnum::FRONT_STORAGE);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if required random player assets are inactive in a storage');
    }

    public function testShouldReturnTrueIfRequiredRandomPlayerAssetsAreActiveInStorageAndTraderIsMush(): void
    {
        $this->givenStorageRoomsInDaedalus();
        $this->givenTradeOptionRequiringRandomPlayer(1);
        $this->givenActivePlayerInStorage(RoomEnum::FRONT_STORAGE);
        $this->givenTraderIsMush($this->trader);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if required random player assets are active in a storage and trader is mush');
    }

    public function testShouldReturnFalseIfRequiredDaedalusVariableAssetsIsNotInExpectedQuantity(): void
    {
        $this->givenTradeOptionRequiringDaedalusVariable(DaedalusVariableEnum::OXYGEN, 10);
        $this->givenDaedalusVariableQuantity(DaedalusVariableEnum::OXYGEN, 9);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldNotBeExecutable($canExecuteTrade, 'Trade option should not be valid if required daedalus variable assets is not in expected quantity');
    }

    public function testShouldReturnTrueIfRequiredDaedalusVariableAssetsIsInExpectedQuantity(): void
    {
        $this->givenTradeOptionRequiringDaedalusVariable(DaedalusVariableEnum::OXYGEN, 10);
        $this->givenDaedalusVariableQuantity(DaedalusVariableEnum::OXYGEN, 10);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if required daedalus variable assets is in expected quantity');
    }

    public function testShouldReturnTrueIfRequiredSpecificPlayerAssetsAreHighlyInactive(): void
    {
        $this->givenTradeOptionRequiringSpecificPlayer(CharacterEnum::IAN, 1);
        $this->givenHighlyInactiveSpecificPlayer(CharacterEnum::IAN);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if required specific player assets are highly inactive');
    }

    public function testShouldReturnFalseIfRequiredSpecificPlayerAssetsAreNotHighlyInactive(): void
    {
        $this->givenTradeOptionRequiringSpecificPlayer(CharacterEnum::IAN, 1);
        $this->givenActiveSpecificPlayer(CharacterEnum::IAN);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldNotBeExecutable($canExecuteTrade, 'Trade option should not be valid if required specific player assets are not highly inactive');
    }

    public function testShouldReturnTrueIfRequiredSpecificPlayerAssetsAreInactiveInStorage(): void
    {
        $this->givenTradeOptionRequiringSpecificPlayer(CharacterEnum::IAN, 1);
        $this->givenInactiveSpecificPlayerInStorage(CharacterEnum::IAN, RoomEnum::FRONT_STORAGE);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if required specific player assets are inactive in a storage');
    }

    public function testShouldReturnTrueIfRequiredSpecificPlayerAssetsAreActiveInStorageAndTraderIsMush(): void
    {
        $this->givenTradeOptionRequiringSpecificPlayer(CharacterEnum::IAN, 1);
        $this->givenActiveSpecificPlayerInStorage(CharacterEnum::IAN, RoomEnum::FRONT_STORAGE);
        $this->givenTraderIsMush($this->trader);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if required specific player assets are active in a storage and trader is mush');
    }

    public function testShouldReturnFalseIfRequiredRandomProjectAssetsAreNotInExpectedQuantity(): void
    {
        $this->givenTradeOptionRequiringRandomProject(quantity: 1);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldNotBeExecutable($canExecuteTrade, 'Trade option should not be valid if required random project assets are not in expected quantity');
    }

    public function testShouldReturnTrueIfRequiredRandomProjectAssetsAreInExpectedQuantity(): void
    {
        $this->givenTradeOptionRequiringRandomProject(quantity: 1);
        $this->givenFinishedProjectsForDaedalus(quantity: 1);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if required random project assets are in expected quantity');
    }

    public function testShouldReturnTrueWithTradeRequiringMultipleAssets(): void
    {
        $this->givenStorageRoomsInDaedalus();
        $this->tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(type: TradeAssetEnum::ITEM, assetName: ItemEnum::HYDROPOT, quantity: 1),
                new TradeAsset(type: TradeAssetEnum::RANDOM_PLAYER, quantity: 1),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($this->tradeOption);

        $this->givenItemsInStorage(ItemEnum::HYDROPOT, [RoomEnum::CENTER_BRAVO_STORAGE]);
        $this->givenInactivePlayerInStorage(RoomEnum::FRONT_STORAGE);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldBeExecutable($canExecuteTrade, 'Trade option should be valid if it requires multiple assets');
    }

    public function testShouldReturnFalseWithTradeRequiringMultipleAssetsWhenOnlyOneIsMet(): void
    {
        $this->givenStorageRoomsInDaedalus();
        $this->tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(type: TradeAssetEnum::ITEM, assetName: ItemEnum::HYDROPOT, quantity: 1),
                new TradeAsset(type: TradeAssetEnum::RANDOM_PLAYER, quantity: 1),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($this->tradeOption);

        // Only provide the item, but not the random player
        $this->givenItemsInStorage(ItemEnum::HYDROPOT, [RoomEnum::CENTER_BRAVO_STORAGE]);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldNotBeExecutable($canExecuteTrade, 'Trade option should not be valid if only one of multiple required assets is met');
    }

    public function testShouldReturnFalseIfAskedPlayerNotOnDaedalus(): void
    {
        $this->daedalus->getGameConfig()->getCharactersConfig()->removeElement(CharacterEnum::IAN);
        $this->givenTradeOptionRequiringSpecificPlayer(CharacterEnum::IAN, 1);

        $canExecuteTrade = $this->whenCheckingTradeConditions();

        $this->thenTradeShouldNotBeExecutable($canExecuteTrade, 'Trade option should not be valid if asked player is not on daedalus');
    }

    private function givenTradeOptionRequiringSkill(SkillEnum $skillName): void
    {
        $this->tradeOption = new TradeOption(
            requiredSkill: $skillName,
            requiredAssets: [],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($this->tradeOption);
    }

    private function givenTradeOptionWithoutRequirements(): void
    {
        $this->tradeOption = new TradeOption(
            offeredAssets: [],
            requiredAssets: [],
        );
        $this->tradeOptionRepository->save($this->tradeOption);
    }

    private function givenTradeOptionRequiringItem(string $itemName, int $quantity): void
    {
        $this->tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(
                    type: TradeAssetEnum::ITEM,
                    assetName: $itemName,
                    quantity: $quantity,
                ),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($this->tradeOption);
    }

    private function givenTradeOptionRequiringSkillAndItem(SkillEnum $skillName, string $itemName, int $quantity): void
    {
        $this->tradeOption = new TradeOption(
            requiredSkill: $skillName,
            requiredAssets: [
                new TradeAsset(
                    type: TradeAssetEnum::ITEM,
                    assetName: $itemName,
                    quantity: $quantity,
                ),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($this->tradeOption);
    }

    private function givenTradeOptionRequiringRandomPlayer(int $quantity): void
    {
        $this->tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(
                    type: TradeAssetEnum::RANDOM_PLAYER,
                    quantity: $quantity,
                ),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($this->tradeOption);
    }

    private function givenTradeOptionRequiringDaedalusVariable(string $variableName, int $quantity): void
    {
        $this->tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(
                    type: TradeAssetEnum::DAEDALUS_VARIABLE,
                    quantity: $quantity,
                    assetName: $variableName,
                ),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($this->tradeOption);
    }

    private function givenTradeOptionRequiringSpecificPlayer(string $characterName, int $quantity): void
    {
        $this->tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(
                    type: TradeAssetEnum::SPECIFIC_PLAYER,
                    quantity: $quantity,
                    assetName: $characterName,
                ),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($this->tradeOption);
    }

    private function givenPlayerInTraderRoomWithSkill(SkillEnum $skillName): void
    {
        $player2 = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        Skill::createByNameForPlayer($skillName, $player2);
    }

    private function givenStorageRoomsInDaedalus(): void
    {
        foreach (RoomEnum::getStorages() as $storage) {
            Place::createRoomByNameInDaedalus($storage, $this->daedalus);
        }
    }

    private function givenItemsInStorage(string $itemName, array $storageRooms): void
    {
        foreach ($storageRooms as $storageRoom) {
            $storage = $this->daedalus->getPlaceByNameOrThrow($storageRoom);
            GameEquipmentFactory::createEquipmentByNameForHolder(
                name: $itemName,
                holder: $storage,
            );
        }
    }

    private function givenHighlyInactivePlayerInDaedalus(): void
    {
        $player2 = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::HIGHLY_INACTIVE,
            holder: $player2,
        );
    }

    private function givenInactivePlayerInStorage(string $storageName): void
    {
        $storage = $this->daedalus->getPlaceByNameOrThrow($storageName);
        $player2 = PlayerFactory::createPlayerInPlace($storage);
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::INACTIVE,
            holder: $player2,
        );
    }

    private function givenActivePlayerInStorage(string $storageName): void
    {
        $storage = $this->daedalus->getPlaceByNameOrThrow($storageName);
        PlayerFactory::createPlayerInPlace($storage);
    }

    private function givenTraderIsMush(Player $player): void
    {
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::MUSH,
            holder: $player,
        );
    }

    private function givenDaedalusVariableQuantity(string $variableName, int $quantity): void
    {
        $this->daedalus->setVariableValueByName($quantity, $variableName);
    }

    private function givenHighlyInactiveSpecificPlayer(string $characterName): void
    {
        $player = PlayerFactory::createPlayerByNameAndDaedalus($characterName, $this->daedalus);
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::HIGHLY_INACTIVE,
            holder: $player,
        );
    }

    private function givenActiveSpecificPlayer(string $characterName): void
    {
        PlayerFactory::createPlayerByNameAndDaedalus($characterName, $this->daedalus);
    }

    private function givenInactiveSpecificPlayerInStorage(string $characterName, string $storageName): void
    {
        $storage = Place::createRoomByNameInDaedalus($storageName, $this->daedalus);
        $player = PlayerFactory::createPlayerByNameAndPlace($characterName, $storage);
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::INACTIVE,
            holder: $player,
        );
    }

    private function givenActiveSpecificPlayerInStorage(string $characterName, string $storageName): void
    {
        $storage = Place::createRoomByNameInDaedalus($storageName, $this->daedalus);
        PlayerFactory::createPlayerByNameAndPlace($characterName, $storage);
    }

    private function givenTradeOptionRequiringRandomProject(int $quantity): void
    {
        $this->tradeOption = new TradeOption(
            requiredAssets: [
                new TradeAsset(
                    type: TradeAssetEnum::RANDOM_PROJECT,
                    quantity: $quantity,
                ),
            ],
            offeredAssets: [],
        );
        $this->tradeOptionRepository->save($this->tradeOption);
    }

    private function givenFinishedProjectsForDaedalus(int $quantity): void
    {
        for ($i = 0; $i < $quantity; ++$i) {
            $project = ProjectFactory::createDummyNeronProjectForDaedalus($this->daedalus);
            $project->finish();
        }
    }

    private function whenCheckingTradeConditions(): bool
    {
        return $this->areTradeOptionConditionsAreMet->execute($this->trader, $this->tradeOption->getId());
    }

    private function thenTradeShouldBeExecutable(bool $canExecuteTrade, string $message): void
    {
        self::assertTrue($canExecuteTrade, $message);
    }

    private function thenTradeShouldNotBeExecutable(bool $canExecuteTrade, string $message): void
    {
        self::assertFalse($canExecuteTrade, $message);
    }
}
