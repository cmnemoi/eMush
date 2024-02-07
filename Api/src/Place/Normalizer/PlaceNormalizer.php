<?php

namespace Mush\Place\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PlaceNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Place;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Place $room */
        $room = $object;

        if (!($currentPlayer = $context['currentPlayer'] ?? [])) {
            throw new \LogicException('Current player is missing from context');
        }

        $players = $this->normalizePlayers(
            $room,
            $currentPlayer,
            $format,
            $context
        );

        $doors = $this->normalizeDoors(
            $room,
            $format,
            $context
        );

        $statuses = $this->normalizeStatuses(
            $room,
            $format,
            $context
        );

        // Split equipments between items and equipments
        $partition = $room->getEquipments()->partition(fn (int $key, GameEquipment $gameEquipment) => ($gameEquipment->getClassName() === GameEquipment::class
            && !EquipmentEnum::equipmentToNormalizeAsItems()->contains($gameEquipment->getName())));

        $equipments = $partition[0];
        $items = $partition[1];

        // do not normalize doors
        $items = $items->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getClassName() !== Door::class);

        $normalizedEquipments = $this->normalizeEquipments(
            $currentPlayer,
            $equipments,
            $format,
            $context
        );
        // remove updatedAt from the normalized equipments because it's not needed in the response
        $normalizedEquipments = array_map(fn (array $equipment) => array_diff_key($equipment, ['updatedAt' => null]), $normalizedEquipments);

        $normalizedItems = $this->normalizeItems($items, $currentPlayer, $format, $context);

        $language = $room->getDaedalus()->getLanguage();

        $currentPlayerIsFocused = $currentPlayer->getFocusedTerminal() !== null;

        return [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => $this->translationService->translate($room->getName() . '.name', [], 'rooms', $language),
            'statuses' => !$currentPlayerIsFocused ? $statuses : [],
            'doors' => !$currentPlayerIsFocused ? $doors : [],
            'players' => !$currentPlayerIsFocused ? $players : [],
            'items' => !$currentPlayerIsFocused ? $normalizedItems : [],
            'equipments' => !$currentPlayerIsFocused ? $normalizedEquipments : [],
            'type' => $room->getType(),
        ];
    }

    private function normalizePlayers(Place $room, Player $currentPlayer, ?string $format, array $context): array
    {
        $players = [];
        /** @var Player $player */
        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            if ($currentPlayer !== $player) {
                $players[] = $this->normalizer->normalize($player, $format, $context);
            }
        }

        return $players;
    }

    private function normalizeStatuses(Place $room, ?string $format, array $context): array
    {
        $statuses = [];
        /** @var Status $status */
        foreach ($room->getStatuses() as $status) {
            if ($status->getVisibility() === VisibilityEnum::PUBLIC) {
                $statuses[] = $this->normalizer->normalize($status, $format, $context);
            }
        }

        return $statuses;
    }

    private function normalizeDoors(Place $room, ?string $format, array $context): array
    {
        $doors = [];
        /** @var Door $door */
        foreach ($room->getDoors() as $door) {
            $normedDoor = $this->normalizer->normalize($door, $format, $context);
            if (is_array($normedDoor)) {
                $doors[] = array_merge(
                    $normedDoor,
                    ['direction' => $door
                        ->getRooms()
                        ->filter(fn (Place $doorRoom) => $doorRoom !== $room)
                        ->first()
                        ->getName(),
                    ]
                );
            }
        }

        return $doors;
    }

    private function normalizeEquipments(
        Player $currentPlayer,
        Collection $equipments,
        ?string $format,
        array $context
    ): array {
        $normalizedEquipments = [];
        /** @var GameEquipment $equipment */
        foreach ($equipments as $equipment) {
            if (!($equipment->getEquipment()->isPersonal() && $equipment->getOwner() !== $currentPlayer)) {
                $normalizedEquipments[] = $this->normalizer->normalize($equipment, $format, $context);
            }
        }

        return $normalizedEquipments;
    }

    private function normalizeItems(Collection $items, Player $currentPlayer, ?string $format, array $context): array
    {
        $piles = [];

        // For each group of item
        foreach ($this->groupItemCollectionByName($items, $currentPlayer) as $itemGroup) {
            /** @var GameItem $patron */
            $patron = $itemGroup->first();

            $patronConfig = $patron->getEquipment();

            if ($patronConfig instanceof ItemConfig) {
                // If not stackable, normalize each occurrence of the item
                if (!$patronConfig->isStackable()) {
                    $piles = $this->handleNonStackableItem(
                        $itemGroup,
                        $currentPlayer,
                        $format,
                        $context,
                        $piles
                    );
                } elseif (!($patronConfig->isPersonal() && $patron->getOwner() !== $currentPlayer)) {
                    $piles = $this->handleStackableItem(
                        $itemGroup,
                        $currentPlayer,
                        $format,
                        $context,
                        $piles
                    );
                }
            } else {
                $piles = $this->handleNonStackableItem(
                    $itemGroup,
                    $currentPlayer,
                    $format,
                    $context,
                    $piles
                );
            }
        }

        // Sort items in a stack fashion in shelves : last in, first out
        usort($piles, fn (array $a, array $b) => $a['updatedAt'] <=> $b['updatedAt']);

        // remove updatedAt from the normalized items because it's not needed in the response
        return array_map(fn (array $item) => array_diff_key($item, ['updatedAt' => null]), $piles);
    }

    private function handleNonStackableItem(
        ArrayCollection $itemGroup,
        Player $currentPlayer,
        ?string $format,
        array $context,
        array $piles
    ): array {
        foreach ($itemGroup as $item) {
            if (!($item->getEquipment()->isPersonal() && $item->getOwner() !== $currentPlayer)) {
                $piles[] = $this->normalizer->normalize($item, $format, $context);
            }
        }

        // Sort items in a stack fashion in shelves : last in, first out
        usort($piles, fn (array $a, array $b) => $a['updatedAt'] <=> $b['updatedAt']);

        return $piles;
    }

    private function handleStackableItem(
        ArrayCollection $itemGroup,
        Player $currentPlayer,
        ?string $format,
        array $context,
        array $piles
    ): array {
        $statusesPiles = $this->groupByStatus($itemGroup, $currentPlayer);

        $oldestItemUpdatedDate = new \DateTime();
        foreach ($statusesPiles as $pileName => $statusesPile) {
            $item = current($statusesPile);

            // The updated time of the pile is when the oldest item was updated
            // so the pile always stays at the same place in the shelf until the last item of it is picked up
            $oldestItemUpdatedDate = $item->getUpdatedAt() < $oldestItemUpdatedDate ? $item->getUpdatedAt() : $oldestItemUpdatedDate;

            /** @var array $normalizedItem */
            $normalizedItem = $this->normalizer->normalize($item, $format, $context);
            $normalizedItem['updatedAt'] = $oldestItemUpdatedDate;

            $countItem = count($statusesPile);
            if ($countItem > 1) {
                $normalizedItem['number'] = $countItem;
            }
            $piles[] = $normalizedItem;
        }

        return $piles;
    }

    // Group item by name
    private function groupItemCollectionByName(Collection $items, Player $currentPlayer): array
    {
        $itemsGroup = [];

        /** @var GameItem $item */
        foreach ($items as $item) {
            // Do not include items hidden to the player
            $hiddenStatus = $item->getStatusByName(EquipmentStatusEnum::HIDDEN);
            if (!$hiddenStatus || ($hiddenStatus->getTarget() === $currentPlayer)) {
                $name = $item->getName();

                // book and blueprint hae the same name event for similar blueprint This part split them
                $book = $item->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BOOK);
                if ($book instanceof Book) {
                    $name .= $book->getSkill();
                }
                $blueprint = $item->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT);
                if ($blueprint instanceof Blueprint) {
                    $name .= $blueprint->getCraftedEquipmentName();
                }

                if (!isset($itemsGroup[$name])) {
                    $itemsGroup[$name] = new ArrayCollection();
                }
                /** @var Collection $currentCollection */
                $currentCollection = $itemsGroup[$name];
                $currentCollection->add($item);
            }
        }

        return $itemsGroup;
    }

    /**
     * Given a collection of gameItem, group them by status in an array.
     */
    private function groupByStatus(Collection $itemsGroup, Player $currentPlayer): array
    {
        $pile = [];
        /** @var GameItem $item */
        foreach ($itemsGroup as $item) {
            $pileName = $this->getPileName($item, $currentPlayer);
            if (!isset($pile[$pileName])) {
                $pile[$pileName] = [];
            }
            $pile[$pileName][] = $item;
        }

        return $pile;
    }

    /**
     * Return the name of the pile for a given item.
     */
    private function getPileName(GameItem $item, Player $currentPlayer): string
    {
        $itemStatuses = $item->getStatuses();
        $pileName = null;

        $statusesFilter = EquipmentStatusEnum::splitItemPileStatus();
        $statusesFilter[] = EquipmentStatusEnum::DOCUMENT_CONTENT;
        if ($currentPlayer->isMush()) {
            $statusesFilter[] = EquipmentStatusEnum::CONTAMINATED;
        }

        $statusesName = $itemStatuses->filter(fn (Status $status) => in_array($status->getName(), $statusesFilter));

        if (!$statusesName->isEmpty()) {
            /** @var Status $status */
            $status = $statusesName->first();
            $pileName = ($status instanceof ContentStatus) ? $status->getContent() : $status->getName();
        }

        return $pileName ?? 'no_status';
    }
}
