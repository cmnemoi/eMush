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
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class PlaceNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
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

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Place $room */
        $room = $object;

        if (!($currentPlayer = $context['currentPlayer'] ?? null)) {
            throw new \LogicException('Current player is missing from context');
        }

        $players = [];
        /** @var Player $player */
        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            if ($currentPlayer !== $player) {
                $players[] = $this->normalizer->normalize($player, $format, $context);
            }
        }

        $doors = [];
        /** @var Door $door */
        foreach ($room->getDoors() as $door) {
            $normedDoor = $this->normalizer->normalize($door, $format, $context);
            if (is_array($normedDoor)) {
                $doors[] = array_merge(
                    $normedDoor,
                    ['direction' => $this->translationService->translate($door
                        ->getRooms()
                        ->filter(fn (Place $doorRoom) => $doorRoom !== $room)
                        ->first()
                        ->getName() . '.name', [], 'rooms'),
                    ]
                );
            }
        }

        $statuses = [];
        /** @var Status $status */
        foreach ($room->getStatuses() as $status) {
            if ($status->getVisibility() === VisibilityEnum::PUBLIC) {
                $statuses[] = $this->normalizer->normalize($status, $format, $context);
            }
        }

        // Split equipments between items and equipments
        $partition = $room->getEquipments()->partition(fn (int $key, GameEquipment $gameEquipment) => $gameEquipment->getClassName() === GameEquipment::class);

        $equipments = $partition[0];
        $items = $partition[1];

        $normalizedEquipments = [];
        /** @var GameEquipment $equipment */
        foreach ($equipments as $equipment) {
            $normalizedEquipments[] = $this->normalizer->normalize($equipment, $format, $context);
        }

        $normalizedItems = $this->getItems($items, $currentPlayer, $format, $context);

        return [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => $this->translationService->translate($room->getName() . '.name', [], 'rooms'),
            'statuses' => $statuses,
            'doors' => $doors,
            'players' => $players,
            'items' => $normalizedItems,
            'equipments' => $normalizedEquipments,
        ];
    }

    private function getItems(Collection $items, Player $currentPlayer, ?string $format, array $context): array
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
                    foreach ($itemGroup as $item) {
                        $piles[] = $this->normalizer->normalize($item, $format, $context);
                    }
                } else {
                    /** @var array $normalizedItem */
                    $statusesPiles = $this->groupByStatus($itemGroup, $currentPlayer);

                    foreach ($statusesPiles as $pileName => $statusesPile) {
                        $item = current($statusesPile);
                        /** @var array $normalizedItem */
                        $normalizedItem = $this->normalizer->normalize($item, $format, $context);

                        $countItem = count($statusesPile);
                        if ($countItem > 1) {
                            $normalizedItem['number'] = $countItem;
                        }
                        $piles[] = $normalizedItem;
                    }
                }
            }
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
                    $name = $name . $book->getSkill();
                }
                $blueprint = $item->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT);
                if ($blueprint instanceof Blueprint) {
                    $name = $name . $blueprint->getEquipment()->getName();
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
