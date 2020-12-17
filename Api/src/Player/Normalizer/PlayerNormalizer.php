<?php

namespace Mush\Player\Normalizer;

use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTargetEnum;
use Mush\Action\Normalizer\ActionNormalizer;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
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
    private PlayersNormalizer $playersNormalizer;

    public function __construct(
        DaedalusNormalizer $daedalusNormalizer,
        RoomNormalizer $roomNormalizer,
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage,
        PlayersNormalizer $playersNormalizer
        
    ) {
        $this->daedalusNormalizer = $daedalusNormalizer;
        $this->roomNormalizer = $roomNormalizer;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->playersNormalizer = $playersNormalizer;
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
        return [
            'gameStatus' => $player->getGameStatus(),
            'daedalus' => $this->daedalusNormalizer->normalize($player->getDaedalus()),
            'player' => $this->playersNormalizer->normalize($player),
            'room' => $this->roomNormalizer->normalize($player->getRoom()),
        ];
    }
}
