<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Psr\Log\LoggerInterface;

class ClosedDaedalusNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'CLOSED_DAEDALUS_NORMALIZER_ALREADY_CALLED';

    private TranslationServiceInterface $translationService;
    private LoggerInterface $logger;

    public function __construct(
        TranslationServiceInterface $translationService,
        LoggerInterface $logger
    ) {
        $this->translationService = $translationService;
        $this->logger = $logger;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof ClosedDaedalus;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        /** @var ClosedDaedalus $daedalus */
        $daedalus = $object;

        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (!is_array($data)) {
            $errorMessage = 'ClosedDaedalusNormalizer::normalize() - normalized closedDaedalus should be an array';
            $this->logger->error($errorMessage, ['closedDaedalus' => $daedalus]);
            throw new \Error($errorMessage);
        }

        return $data;
    }
}
