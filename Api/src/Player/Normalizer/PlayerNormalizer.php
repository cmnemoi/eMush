<?php

namespace Mush\Player\Normalizer;

use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Player\Entity\Player;
use Mush\Room\Normalizer\RoomNormalizer;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayerNormalizer implements ContextAwareNormalizerInterface
{
    private DaedalusNormalizer $daedalusNormalizer;
    private RoomNormalizer $roomNormalizer;
    private TranslatorInterface $translator;
    private TokenStorageInterface $tokenStorage;
    private EquipmentNormalizer $equipmentNormalizer;

    public function __construct(
        DaedalusNormalizer $daedalusNormalizer,
        RoomNormalizer $roomNormalizer,
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage,
        EquipmentNormalizer $equipmentNormalizer
    ) {
        $this->daedalusNormalizer = $daedalusNormalizer;
        $this->roomNormalizer = $roomNormalizer;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->equipmentNormalizer = $equipmentNormalizer;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Player;
    }

    /**
     * @param Player $player
     *
     * @return array
     */
    public function normalize($player, string $format = null, array $context = [])
    {
        $playerPersonalInfo = [];
        if ($this->getUser()->getCurrentGame() === $player) {
            $items = [];
            /** @var GameItem $item */
            foreach ($player->getItems() as $item) {
                $items[] = $this->equipmentNormalizer->normalize($item);
            }

            $playerPersonalInfo = [
                'items' => $items,
                'actionPoint' => $player->getActionPoint(),
                'movementPoint' => $player->getMovementPoint(),
                'healthPoint' => $player->getHealthPoint(),
                'moralPoint' => $player->getMoralPoint(),
                'triumph' => $player->getTriumph(),
                'createdAt' => $player->getCreatedAt(),
                'updatedAt' => $player->getUpdatedAt(),
            ];
        }

        return array_merge([
            'id' => $player->getId(),
            'character' => [
                'key' => $player->getPerson(),
                'value' => $this->translator->trans($player->getPerson() . '.name', [], 'characters'),
            ],
            'gameStatus' => $player->getGameStatus(),
            'statuses' => $player->getStatuses(),
            'daedalus' => $this->daedalusNormalizer->normalize($player->getDaedalus()),
            'room' => $this->roomNormalizer->normalize($player->getRoom()),
            'skills' => $player->getSkills(),
        ], $playerPersonalInfo);
    }

    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
