<?php

namespace Mush\Player\Normalizer;

use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ClosedPlayerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'CLOSED_PLAYER_NORMALIZER_ALREADY_CALLED';

    private CycleServiceInterface $cycleService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        CycleServiceInterface $cycleService,
        TranslationServiceInterface $translationService,
    ) {
        $this->cycleService = $cycleService;
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

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var ClosedPlayer $closedPlayer */
        $closedPlayer = $object;

        $daedalus = $closedPlayer->getClosedDaedalus();

        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (!is_array($data)) {
            throw new \Exception('ClosedPlayerNormalizer: data is not an array');
        }

        if ($daedalus->isDaedalusFinished()) {
            $data['endCause'] = $this->getTranslatedEndCause($closedPlayer->getEndCause(), $daedalus->getDaedalusInfo()->getLanguage());

            $createdAt = $closedPlayer->getCreatedAt();
            if ($createdAt === null) {
                throw new \Exception('ClosedPlayer createdAt should not be null');
            }
            $finishedAt = $closedPlayer->getFinishedAt();
            if ($finishedAt === null) {
                throw new \Exception('ClosedPlayer finishedAt should not be null');
            }

            $data['cyclesSurvived'] = $this->cycleService->getNumberOfCycleElapsed(
                start: $createdAt,
                end: $finishedAt,
                daedalusInfo: $closedPlayer->getClosedDaedalus()->getDaedalusInfo()
            );
            $data['daysSurvived'] = intval($data['cyclesSurvived'] / $daedalus->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getCyclePerGameDay());
        }

        return $data;
    }

    private function getTranslatedEndCause(string $endCause, string $language): array
    {
        return [
            'key' => $endCause,
            'name' => $this->translationService->translate(
                key: $endCause . '.name',
                parameters: [],
                domain: 'end_cause',
                language: $language
            ),
            'shortName' => $this->translationService->translate(
                key: $endCause . '.short_name',
                parameters: [],
                domain: 'end_cause',
                language: $language
            ),
            'description' => $this->translationService->translate(
                key: $endCause . '.description',
                parameters: [],
                domain: 'end_cause',
                language: $language
            ),
        ];
    }
}
