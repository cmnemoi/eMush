<?php

namespace Mush\Player\Normalizer;

use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class DeadPlayerInfoNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private PlayerServiceInterface $playerService;
    private TranslationServiceInterface $translationService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        PlayerServiceInterface $playerService,
        TranslationServiceInterface $translationService,
        GearToolServiceInterface $gearToolService
    ) {
        $this->playerService = $playerService;
        $this->translationService = $translationService;
        $this->gearToolService = $gearToolService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof ClosedPlayer;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var ClosedPlayer $deadPlayerInfo */
        $deadPlayerInfo = $object;

        return [
            'player' => $deadPlayerInfo->getPlayerInfo()->getName(),
        ];
    }
}
