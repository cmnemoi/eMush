<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ClosedDaedalusNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const string ALREADY_CALLED = 'CLOSED_DAEDALUS_NORMALIZER_ALREADY_CALLED';
    private CycleServiceInterface $cycleService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        CycleServiceInterface $cycleService,
        TranslationServiceInterface $translationService
    ) {
        $this->cycleService = $cycleService;
        $this->translationService = $translationService;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ClosedDaedalus::class => false,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof ClosedDaedalus;
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        /** @var ClosedDaedalus $daedalus */
        $daedalus = $object;

        $context[self::ALREADY_CALLED] = true;

        $normalizedDaedalus = $this->normalizer->normalize($object, $format, $context);

        if (!\is_array($normalizedDaedalus)) {
            throw new \Exception('normalized closedDaedalus should be an array');
        }

        if (!$daedalus->isDaedalusFinished()) {
            return $normalizedDaedalus;
        }

        $normalizedDaedalus['cyclesSurvived'] = $this->cycleService->getNumberOfCycleElapsed(
            start: $daedalus->getCreatedAtOrThrow(),
            end: $daedalus->getFinishedAtOrThrow(),
            daedalusInfo: $daedalus->getDaedalusInfo()
        );
        $normalizedDaedalus['statistics'] = $this->getNormalizedStatistics($daedalus);

        return $normalizedDaedalus;
    }

    private function getNormalizedStatistics(ClosedDaedalus $daedalus): array
    {
        $normalizedStatistics = [];
        $normalizedStatistics['title'] = $this->translationService->translate(
            key: 'statistics',
            parameters: [],
            domain: 'the_end',
            language: $daedalus->getLanguage()
        );
        foreach ($daedalus->getDaedalusInfo()->getDaedalusStatistics()->toArray() as $statistic) {
            $normalizedStatistics['lines'][] = [
                'name' => $this->translationService->translate(
                    key: $statistic->name,
                    parameters: [],
                    domain: 'the_end',
                    language: $daedalus->getLanguage()
                ),
                'value' => $statistic->value,
            ];
        }

        return $normalizedStatistics;
    }
}
