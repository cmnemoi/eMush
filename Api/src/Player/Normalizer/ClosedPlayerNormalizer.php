<?php

namespace Mush\Player\Normalizer;

use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ClosedPlayerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'CLOSED_PLAYER_NORMALIZER_ALREADY_CALLED';

    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService,
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof ClosedPlayer;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        /** @var ClosedPlayer $player */
        $player = $object;
        $closedDaedalus = $player->getClosedDaedalus();

        if ($closedDaedalus->getDaedalusInfo()->getGameStatus() === GameStatusEnum::CLOSED) {
            $context['group'][] = 'user_info_read';
        }

        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (is_array($data) && key_exists('endCause', $data)) {
            $data['endCause'] = $this->translationService->translate(
                $data['endCause'] . '.name',
                [],
                LanguageEnum::END_CAUSE,
                LanguageEnum::FRENCH
            );
        } else {
            throw new \Error('normalized closedDaedalus should be an array with a endCause key');
        }

        return $data;
    }
}
