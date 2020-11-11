<?php

namespace Mush\Room\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\GameItem;
use Mush\Item\Normalizer\ItemNormalizer;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Door;
use Mush\Room\Entity\Room;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class RoomNormalizer implements ContextAwareNormalizerInterface
{
    private ItemNormalizer $itemNormalizer;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        ItemNormalizer $itemNormalizer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->itemNormalizer = $itemNormalizer;
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Room;
    }

    /**
     * @param Room $room
     *
     * @return array
     */
    public function normalize($room, string $format = null, array $context = [])
    {
        $players = [];
        /** @var Player $player */
        foreach ($room->getPlayers() as $player) {
            //Do not display user player in the room
            if ($player !== $this->getUser()->getCurrentGame()) {
                $players[] = [
                    'id' => $player->getId(),
                    'name' => $player->getPerson(),
                    'statuses' => $player->getStatuses(),
                    'skills' => $player->getSkills(),
                    'actions' => [ActionEnum::HIT],
                ];
            }
        }
        $doors = [];
        /** @var Door $door */
        foreach ($room->getDoors() as $door) {
            $doors[] = [
                'id' => $door->getId(),
                'name' => $door->getName(),
                'direction' => $door->getRooms()->filter(fn (Room $doorRoom) => $doorRoom !== $room)->first()->getName(),
                'actions' => [ActionEnum::MOVE],
            ];
        }
        $items = [];
        /** @var GameItem $item */
        foreach ($room->getItems() as $item) {
            $items[] = $this->itemNormalizer->normalize($item);
        }

        return [
            'id' => $room->getId(),
            'name' => $room->getName(),
            'doors' => $doors,
            'players' => $players,
            'items' => $items,
            'equipments' => ['TODO'],
            'createdAt' => $room->getCreatedAt(),
            'updatedAt' => $room->getUpdatedAt(),
        ];
    }

    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
