<?php

namespace Mush\Room\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoomNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslatorInterface $translator;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Room;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Room $room */
        $room = $object;
        $players = [];
        /** @var Player $player */
        foreach ($room->getPlayers() as $player) {
            if ($this->getPlayer() !== $player) {
                $players[] = $this->normalizer->normalize($player);
            }
        }

        $doors = [];
        /** @var Door $door */
        foreach ($room->getDoors() as $door) {
            $normedDoor = $this->normalizer->normalize($door);
            if (is_array($normedDoor)) {
                $doors[] = array_merge(
                    $normedDoor,
                    ['direction' => $door
                        ->getRooms()
                        ->filter(fn (Room $doorRoom) => $doorRoom !== $room)
                        ->first()
                        ->getName(),
                    ]
                );
            }
        }

        $statuses = [];
        /** @var Status $status */
        foreach ($room->getStatuses() as $status) {
            if ($status->getVisibility() === VisibilityEnum::PUBLIC) {
                $statuses[] = $this->normalizer->normalize($status);
            }
        }

        //Split equipments between items and equipments
        $partition = $room->getEquipments()->partition(fn (int $key, GameEquipment $gameEquipment) => $gameEquipment->getClassName() === GameEquipment::class);

        $equipments = $partition[0];
        $items = $partition[1];

        $normalizedEquipments = [];
        /** @var GameEquipment $equipment */
        foreach ($equipments as $equipment) {
            $normalizedEquipments[] = $this->normalizer->normalize($equipment);
        }

        $normalizedItems = $this->getItems($items);

        return [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => $this->translator->trans($room->getName() . '.name', [], 'rooms'),
            'statuses' => $statuses,
            'doors' => $doors,
            'players' => $players,
            'items' => $normalizedItems,
            'equipments' => $normalizedEquipments,
        ];
    }

    private function getItems(Collection $items): array
    {
        $piles = [];

        //For each group of item
        foreach ($this->groupItemCollectionByName($items) as $itemGroup) {
            /** @var GameItem $patron */
            $patron = $itemGroup->first();

            $patronConfig = $patron->getEquipment();

            if ($patronConfig instanceof ItemConfig) {
                //If not stackable, normalize each occurence of the item
                if (!$patronConfig->isStackable()) {
                    foreach ($itemGroup as $item) {
                        $piles[] = $this->normalizer->normalize($item);
                    }
                } else {
                    //Only normalize the item reference
                    /** @var array $normalizedItem */
                    $normalizedItem = $this->normalizer->normalize($patron);
                    $statusesPiles = $this->groupByStatus($itemGroup);
                    foreach ($statusesPiles as $pileName => $statusesPile) {
                        $currentNormalizedItem = $normalizedItem;
                        $countItem = count($statusesPile);
                        if ($countItem > 1) {
                            $currentNormalizedItem['number'] = $countItem;
                        }
                        $piles[] = $currentNormalizedItem;
                    }
                }
            }
        }

        return $piles;
    }

    //Group item by name
    private function groupItemCollectionByName(Collection $items): array
    {
        $itemsGroup = [];

        /** @var GameItem $item */
        foreach ($items as $item) {
            //Do not include items hidden to the player
            $hiddenStatus = $item->getStatusByName(EquipmentStatusEnum::HIDDEN);
            if (!$hiddenStatus || ($hiddenStatus->getTarget() === $this->getPlayer())) {
                if (!isset($itemsGroup[$item->getName()])) {
                    $itemsGroup[$item->getName()] = new ArrayCollection();
                }
                /** @var Collection $currentCollection */
                $currentCollection = $itemsGroup[$item->getName()];
                $currentCollection->add($item);
            }
        }

        return $itemsGroup;
    }

    /**
     * Given a collection of gameItem, group them by status in an array.
     */
    private function groupByStatus(Collection $itemsGroup): array
    {
        $pile = [];
        /** @var GameItem $item */
        foreach ($itemsGroup as $item) {
            $pileName = $this->getPileName($item);
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
    private function getPileName(GameItem $item): string
    {
        $itemStatuses = $item->getStatuses();
        $pileName = null;

        $statusesFilter = EquipmentStatusEnum::splitItemPileStatus();
        $statusesFilter[] = EquipmentStatusEnum::DOCUMENT_CONTENT;
        if ($this->getPlayer()->isMush()) {
            $statusesFilter[] = EquipmentStatusEnum::CONTAMINATED;
        }

        $statusesName = $itemStatuses->filter(fn (Status $status) => (in_array($status->getName(), $statusesFilter)));
        if (!$statusesName->isEmpty()) {
            /** @var Status $status */
            $status = $statusesName->first();
            $pileName = ($status instanceof ContentStatus) ? $status->getContent() : $status->getName();
        }

        return $pileName ?? 'no_status';
    }

    private function getPlayer(): Player
    {
        if (!$token = $this->tokenStorage->getToken()) {
            throw new AccessDeniedException('User should be logged to access that');
        }

        /** @var User $user */
        $user = $token->getUser();

        if (!$player = $user->getCurrentGame()) {
            throw new AccessDeniedException('User should be in game to access that');
        }

        return $player;
    }
}
