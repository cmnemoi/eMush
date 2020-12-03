<?php

namespace Mush\Item\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Charged;
use Mush\Item\Entity\Items\Document;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\ItemType;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Repository\GameItemRepository;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class GameItemService implements GameItemServiceInterface
{
    private EntityManagerInterface $entityManager;
    private GameItemRepository $repository;
    private ItemServiceInterface $itemService;
    private StatusServiceInterface $statusService;
    private ItemEffectServiceInterface $itemEffectService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameItemRepository $repository,
        ItemServiceInterface $itemService,
        StatusServiceInterface $statusService,
        ItemEffectServiceInterface $itemEffectService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->itemService = $itemService;
        $this->statusService = $statusService;
        $this->itemEffectService = $itemEffectService;
    }

    public function persist(GameItem $item): GameItem
    {
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $item;
    }

    public function delete(GameItem $item): void
    {
        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }

    public function findById(int $id): ?GameItem
    {
        return $this->repository->find($id);
    }

    public function createGameItemFromName(string $itemName, Daedalus $daedalus): GameItem
    {
        $item = $this->itemService->findByNameAndDaedalus($itemName, $daedalus);

        return $this->createGameItem($item, $daedalus);
    }

    public function createGameItem(Item $item, Daedalus $daedalus): GameItem
    {
        $gameItem = $item->createGameItem();

        /** @var ItemType $type */
        foreach ($item->getTypes() as $type) {
            switch ($type->getType()) {
                case ItemTypeEnum::PLANT:
                    $this->initPlant($gameItem, $type, $daedalus);
                    break;
                case ItemTypeEnum::CHARGED:
                    $this->initCharged($gameItem, $type);
                    break;
                case ItemTypeEnum::DOCUMENT:
                    $this->initDocument($gameItem, $type);
                    break;
            }
        }

        return $this->persist($gameItem);
    }

    // @TODO maybe remove those init functions to directly include them in createGameItem
    private function initPlant(GameItem $gameItem, Plant $plant, Daedalus $daedalus): GameItem
    {
        $this->statusService->createChargeItemStatus(
            ItemStatusEnum::PLANT_YOUNG,
            $gameItem,
            ChargeStrategyTypeEnum::GROWING_PLANT,
            0,
            $this->itemEffectService->getPlantEffect($plant, $daedalus)->getMaturationTime()
        );

        return $gameItem;
    }

    private function initCharged(GameItem $gameItem, Charged $charged): GameItem
    {
        $this->statusService->createChargeItemStatus(
            StatusEnum::CHARGE,
            $gameItem,
            $charged->getChargeStrategy(),
            $charged->getStartCharge(),
            $charged->getMaxCharge()
        );

        return $gameItem;
    }

    private function initDocument(GameItem $gameItem, Document $document): GameItem
    {
        if ($document->getContent()) {
            $contentStatus = new ContentStatus();
            $contentStatus
                ->setName(ItemStatusEnum::DOCUMENT_CONTENT)
                ->setVisibility(VisibilityEnum::PRIVATE)
                ->setGameItem($gameItem)
                ->setContent($document->getContent())
            ;
        }

        return $gameItem;
    }


    //Implement accessibility to item (for tool and gear)
    public function getOperationalItemsByName(string $itemName, Player $player, string $reach): Collection
    {
        //reach can be set to inventory, shelve, shelve only or any room of the Daedalus
        return $reachableItems = $player
            ->getReachableItemsByName($itemName, $reach)
            ->filter(fn (GameItem $gameItem) => $this->isOperational($gameItem))
            ;
    }

    public function isOperational(GameItem $gameItem): bool
    {
        return !($gameItem->getStatusByName(ItemStatusEnum::BROKEN) ||
            (($chargedType = $gameItem->getStatusByName(ItemTypeEnum::CHARGED)) &&
                $chargedType->getCharge() > 0)
        );
    }
}
