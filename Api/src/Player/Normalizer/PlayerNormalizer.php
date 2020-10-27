<?php

namespace Mush\Player\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Normalizer\RoomNormalizer;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class PlayerNormalizer implements ContextAwareNormalizerInterface
{
    private DaedalusNormalizer $daedalusNormalizer;
    private RoomNormalizer $roomNormalizer;
    private TokenStorageInterface $tokenStorage;

    /**
     * PlayerNormalizer constructor.
     * @param DaedalusNormalizer $daedalusNormalizer
     * @param RoomNormalizer $roomNormalizer
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        DaedalusNormalizer $daedalusNormalizer,
        RoomNormalizer $roomNormalizer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->daedalusNormalizer = $daedalusNormalizer;
        $this->roomNormalizer = $roomNormalizer;
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Player;
    }

    /**
     * @param Player $player
     * @param string|null $format
     * @param array $context
     * @return array
     */
    public function normalize($player, string $format = null, array $context = [])
    {
        $playerPersonalInfo = [];
        if ($this->getUser()->getCurrentGame() === $player) {
            $playerPersonalInfo = [
                'actionPoint' => $player->getActionPoint(),
                'movementPoint' => $player->getMovementPoint(),
                'healthPoint' => $player->getHealthPoint(),
                'moralPoint' => $player->getMoralPoint(),
                'createdAt' => $player->getCreatedAt(),
                'updatedAt' => $player->getUpdatedAt()
            ];
        }

        return array_merge([
            'id' => $player->getId(),
            'daedalus' => $this->daedalusNormalizer->normalize($player->getDaedalus()),
            'room' => $this->roomNormalizer->normalize($player->getRoom()),
            'character' => $player->getPerson(),
            'gameStatus' => $player->getGameStatus(),
            'statuses' => $player->getStatuses(),
            'skills' => $player->getSkills(),
        ], $playerPersonalInfo);
    }

    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
