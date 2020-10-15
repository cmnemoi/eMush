<?php

namespace Mush\Player\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class PlayerNormalizer implements ContextAwareNormalizerInterface
{

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
            return [
                'id' => $player->getId(),
                'character' => $player->getPerson(),
                'gameStatus' => $player->getGameStatus(),
                'actionPoint' => $player->getActionPoint(),
                'movementPoint' => $player->getMovementPoint(),
                'healthPoint' => $player->getHealthPoint(),
                'moralPoint' => $player->getMoralPoint(),
                'statuses' => $player->getStatuses(),
                'skills' => $player->getSkills(),
                'createdAt' => $player->getCreatedAt(),
                'updatedAt' => $player->getUpdatedAt()
            ];
    }
}