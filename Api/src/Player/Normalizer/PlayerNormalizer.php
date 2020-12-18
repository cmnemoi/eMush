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

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        //@ TODO : fix that
        return $data instanceof Player && false;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        return [
            'gameStatus' => $object->getGameStatus(),
            'daedalus' => $this->daedalusNormalizer->normalize($object->getDaedalus()),
            'player' => $this->playersNormalizer->normalize($object),
            'room' => $this->roomNormalizer->normalize($object->getRoom()),
        ];
    }
}
