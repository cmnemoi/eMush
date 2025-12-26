<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Repository\TradeOptionRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Exception\GameException;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Project\Service\DeactivateProjectService;

final readonly class ConsumeRequiredTradeAssetsService
{
    public function __construct(
        private DeactivateProjectService $deactivateProject,
        private DeleteEquipmentServiceInterface $deleteEquipmentService,
        private EventServiceInterface $eventService,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
        private PlayerServiceInterface $playerService,
        private TradeOptionRepositoryInterface $tradeOptionRepository,
        private RandomServiceInterface $randomService
    ) {}

    public function execute(Player $trader, int $tradeOptionId): void
    {
        $tradeOption = $this->tradeOptionRepository->findByIdOrThrow($tradeOptionId);
        $daedalus = $trader->getDaedalus();

        foreach ($tradeOption->getRequiredAssets() as $requiredAsset) {
            match ($requiredAsset->getType()) {
                TradeAssetEnum::DAEDALUS_VARIABLE => $this->reduceDaedalusVariable($daedalus, $requiredAsset),
                TradeAssetEnum::ITEM => $this->deleteItemsFromStorages($daedalus, $requiredAsset),
                TradeAssetEnum::RANDOM_PLAYER => $this->killRandomTradablePlayers($trader, $requiredAsset),
                TradeAssetEnum::RANDOM_PROJECT => $this->deactivateRandomProjects($daedalus, $requiredAsset),
                TradeAssetEnum::SPECIFIC_PLAYER => $this->killSpecificPlayer($trader, $requiredAsset),
                default => throw new \RuntimeException('Unhandled trade asset type: ' . $requiredAsset->getType()->value),
            };
        }
    }

    private function reduceDaedalusVariable(Daedalus $daedalus, TradeAsset $requiredAsset): void
    {
        $daedalusVariable = $daedalus->getVariableValueByName($requiredAsset->getAssetName());
        if ($daedalusVariable < $requiredAsset->getQuantity()) {
            throw new GameException("You don't have enough {$requiredAsset->getAssetName()} to trade!");
        }

        $daedalusEvent = new DaedalusVariableEvent(
            daedalus: $daedalus,
            variableName: $requiredAsset->getAssetName(),
            quantity: -$requiredAsset->getQuantity(),
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function deleteItemsFromStorages(Daedalus $daedalus, TradeAsset $requiredAsset): void
    {
        $remainingItemsToDelete = $requiredAsset->getQuantity();
        $itemName = $requiredAsset->getAssetName();

        while ($remainingItemsToDelete > 0) {
            $itemToDelete = $this->findItemInDaedalusStoragesByName($daedalus, $itemName);
            if ($itemToDelete === null) {
                throw new GameException("You don't have enough {$itemName} in storages to trade!");
            }

            $this->deleteEquipmentService->execute($itemToDelete);
            --$remainingItemsToDelete;
        }
    }

    private function killRandomTradablePlayers(Player $trader, TradeAsset $requiredAsset): void
    {
        // count amount of strawmans
        $amountOfStrawmanInStorage = $this->numberOfItemsInDaedalusStorages(ItemEnum::STRAWMAN, $trader->getDaedalus());

        $tradablePlayers = $trader->getDaedalus()->getPlayers()->getTradablePlayersFor($trader);
        $playersToKill = $this->getRandomElementsFromArray->execute(
            elements: $tradablePlayers->toArray(),
            number: $requiredAsset->getQuantity()
        );

        if ($playersToKill->count() + $amountOfStrawmanInStorage < $requiredAsset->getQuantity()) {
            throw new GameException("You don't have enough tradable players!");
        }

        $amount = $requiredAsset->getQuantity();

        // destroy strawmans or players until the amount is good.
        while ($amount > 0) {
            $strawman = $this->findItemInDaedalusStoragesByName($trader->getDaedalus(), ItemEnum::STRAWMAN);
            if ($strawman !== null) {
                $this->deleteEquipmentService->execute($strawman);
                --$amount;

                continue;
            }

            $player = $this->randomService->getRandomElement($playersToKill->toArray());

            $this->playerService->killPlayer(
                player: $player,
                endReason: EndCauseEnum::ALIEN_ABDUCTED,
                author: $trader,
            );

            $playersToKill->removeElement($player);
            --$amount;
        }
    }

    private function killSpecificPlayer(Player $trader, TradeAsset $requiredAsset): void
    {
        // if item strawman in storage, exchange it instead
        $strawman = $this->findItemInDaedalusStoragesByName($trader->getDaedalus(), ItemEnum::STRAWMAN);
        if ($strawman !== null) {
            $this->deleteEquipmentService->execute($strawman);

            return;
        }

        $player = $trader->getDaedalus()->getPlayerByNameOrThrow($requiredAsset->getAssetName());

        if (!$trader->canTradePlayer($player)) {
            throw new GameException("{$trader->getName()} is not tradable (not highly inactive, not in storage, or you're not Mush)!");
        }

        $this->playerService->killPlayer(
            player: $player,
            endReason: EndCauseEnum::ALIEN_ABDUCTED,
            author: $trader,
        );
    }

    private function deactivateRandomProjects(Daedalus $daedalus, TradeAsset $requiredAsset): void
    {
        $randomProjects = $this->getRandomElementsFromArray->execute(
            elements: $daedalus->getFinishedNeronProjects()->toArray(),
            number: $requiredAsset->getQuantity()
        );

        if ($randomProjects->count() < $requiredAsset->getQuantity()) {
            throw new GameException("You don't have enough NERON projects to trade!");
        }

        foreach ($randomProjects as $project) {
            $this->deactivateProject->execute($project);
        }
    }

    private function findItemInDaedalusStoragesByName(Daedalus $daedalus, string $itemName): ?GameEquipment
    {
        foreach ($daedalus->getStorages() as $storage) {
            $item = $storage->getEquipmentByName($itemName);
            if (!$item) {
                continue;
            }

            return $item;
        }

        return null;
    }

    private function numberOfItemsInDaedalusStorages(string $itemName, Daedalus $daedalus): int
    {
        return $daedalus->getStorages()->reduce(
            static function (int $carry, Place $storage) use ($itemName) {
                return $carry + $storage->getAllEquipmentsByName($itemName)->count();
            },
            0
        );
    }
}
