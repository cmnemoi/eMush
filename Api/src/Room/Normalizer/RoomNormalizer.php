<?php

namespace Mush\Room\Normalizer;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Normalizer\ItemPileNormalizer;
use Mush\Player\Entity\Player;
use Mush\Player\Normalizer\PlayersNormalizer;
use Mush\Room\Entity\Room;
use Mush\Status\Normalizer\StatusNormalizer;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoomNormalizer implements ContextAwareNormalizerInterface
{
    private EquipmentNormalizer $equipmentNormalizer;
    private ItemPileNormalizer $itemPileNormalizer;
    private StatusNormalizer $statusNormalizer;
    private PlayersNormalizer $playersNormalizer;
    private TranslatorInterface $translator;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EquipmentNormalizer $equipmentNormalizer,
        ItemPileNormalizer $itemPileNormalizer,
        StatusNormalizer $statusNormalizer,
        PlayersNormalizer $playersNormalizer,
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->equipmentNormalizer = $equipmentNormalizer;
        $this->itemPileNormalizer = $itemPileNormalizer;
        $this->statusNormalizer = $statusNormalizer;
        $this->playersNormalizer = $playersNormalizer;
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
        $room = $object;
        $players = [];
        /** @var Player $player */
        foreach ($room->getPlayers() as $player) {
            //Do not display user player in the room
            if ($player !== $this->getUser()->getCurrentGame()) {
                $players[] = $this->playersNormalizer->normalize($player);
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
        /** @var GameEquipment $equipment */
        foreach ($room->getEquipments() as $equipment) {
            if (!$equipment instanceof GameItem) {
                $equipments[] = $this->equipmentNormalizer->normalize($equipment);
            }
        }

        $items = $this->itemPileNormalizer->normalize($room->getEquipments());

        return [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => $this->translator->trans($room->getName() . '.name', [], 'rooms'),
            'doors' => $doors,
            'players' => $players,
            'items' => $items,
            'equipments' => $equipments,
        ];
    }

    private function getUser(): User
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $user;
    }
}
