<?php

namespace Mush\Player\Normalizer;

use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Player\Entity\Player;
use Mush\Room\Normalizer\RoomNormalizer;
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
