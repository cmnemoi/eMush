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

    private const ALREADY_CALLED = 'CLOSED_DAEDALUS_NORMALIZER_ALREADY_CALLED';

    private CycleServiceInterface $cycleService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        CycleServiceInterface $cycleService,
        TranslationServiceInterface $translationService
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

        return $data instanceof ClosedDaedalus;
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        /** @var ClosedDaedalus $daedalus */
        $daedalus = $object;

        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (!is_array($data)) {
            throw new \Exception('normalized closedDaedalus should be an array');
        }

        /** @var \DateTime $startDate */
        $startDate = $daedalus->getCreatedAt();
        if ($daedalus->isDaedalusFinished()) {
            $data['startCycle'] = $this->cycleService->getInDayCycleFromDate($startDate, $daedalus);
        }

        return $data;
    }
}
