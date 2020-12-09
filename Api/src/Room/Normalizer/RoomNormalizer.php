<?php

namespace Mush\Room\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoomNormalizer implements ContextAwareNormalizerInterface
{
    private EquipmentNormalizer $equipmentNormalizer;
    private TranslatorInterface $translator;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EquipmentNormalizer $equipmentNormalizer,
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->equipmentNormalizer = $equipmentNormalizer;
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
                $this->equipmentNormalizer->normalize($door),
                ['direction' => $door
                    ->getRooms()
                    ->filter(fn (Room $doorRoom) => $doorRoom !== $room)
                    ->first()
                    ->getName(), ]
            );
        }

        $equipments = [];
        $items = [];
        /** @var GameEquipment $equipment */
        foreach ($room->getEquipments() as $equipment) {
            if ($equipment instanceof GameItem) {
                $items[] = $this->equipmentNormalizer->normalize($equipment);
            } else {
                $equipments[] = $this->equipmentNormalizer->normalize($equipment);
            }
        }

        return [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => $this->translator->trans($room->getName() . '.name', [], 'rooms'),
            'doors' => $doors,
            'players' => $players,
            'items' => $items,
            'equipments' => $equipments,
            'createdAt' => $room->getCreatedAt(),
            'updatedAt' => $room->getUpdatedAt(),
        ];
    }

    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
