<?php

namespace Mush\Room\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\Door;
use Mush\Item\Entity\GameItem;
use Mush\Item\Normalizer\ItemNormalizer;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoomNormalizer implements ContextAwareNormalizerInterface
{
    private ItemNormalizer $itemNormalizer;
    private TranslatorInterface $translator;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        ItemNormalizer $itemNormalizer,
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->itemNormalizer = $itemNormalizer;
        $this->translator = $translator;
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
                    'name' => $this->translator->trans($player->getPerson() . '.name', [], 'characters'),
                    'statuses' => $player->getStatuses(),
                    'skills' => $player->getSkills(),
                    'actions' => [ActionEnum::HIT],
                ];
            }
        }
        $doors = [];
        /** @var Door $door */
        foreach ($room->getDoors() as $door) {
            $doors[] = array_merge(
                $this->itemNormalizer->normalize($door),
                ['direction' => $door
                    ->getRooms()
                    ->filter(fn (Room $doorRoom) => $doorRoom !== $room)
                    ->first()
                    ->getName()]
            );
        }
        $items = [];
        /** @var GameItem $item */
        foreach ($room->getItems() as $item) {
            $items[] = $this->itemNormalizer->normalize($item);
        }

        return [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => $this->translator->trans($room->getName() . '.name', [], 'rooms'),
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
