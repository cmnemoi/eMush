<?php

namespace Mush\Room\Normalizer;

use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoomNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslatorInterface $translator;
    private ActionServiceInterface $actionService;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        TranslatorInterface $translator,
        ActionServiceInterface $actionService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->translator = $translator;
        $this->actionService = $actionService;
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
            if ($this->getUser()->getCurrentGame() !== $player) {
                $players[] = $this->normalizer->normalize($player);
            }
        }

        $doors = [];
        /** @var Door $door */
        foreach ($room->getDoors() as $door) {
            $doors[] = array_merge(
                $this->normalizer->normalize($door),
                ['direction' => $door
                    ->getRooms()
                    ->filter(fn (Room $doorRoom) => $doorRoom !== $room)
                    ->first()
                    ->getName(),
                ]
            );
        }

        $equipments = [];
        /** @var GameEquipment $equipment */
        foreach ($room->getEquipments() as $equipment) {
            if (!$equipment instanceof GameItem) {
                $equipments[] = $this->normalizer->normalize($equipment);
            }
        }

        $items = $this->normalizer->normalize($room->getEquipments());

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
