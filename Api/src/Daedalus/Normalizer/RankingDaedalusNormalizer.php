<?php

declare(strict_types=1);

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\ViewModel\RankingDaedalusViewModel;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class RankingDaedalusNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization(mixed $data, ?string $format = null)
    {
        return $data instanceof RankingDaedalusViewModel;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            RankingDaedalusViewModel::class => true,
        ];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var RankingDaedalusViewModel $object */
        $object = $object;

        return [
            'id' => $object->daedalusId,
            'endCause' => $this->translationService->translate(
                key: \sprintf('%s.name', $object->endCause),
                parameters: [],
                domain: 'end_cause',
                language: $object->daedalusLanguage,
            ),
            'daysSurvived' => $object->daysSurvived,
            'cyclesSurvived' => $object->cyclesSurvived,
            'humanTriumphSum' => \sprintf('%d :triumph:', $object->humanTriumphSum),
            'mushTriumphSum' => \sprintf('%d :triumph_mush:', $object->mushTriumphSum),
            'highestHumanTriumph' => \sprintf('%d :triumph:', $object->highestHumanTriumph),
            'highestMushTriumph' => \sprintf('%d :triumph_mush:', $object->highestMushTriumph),
        ];
    }
}
