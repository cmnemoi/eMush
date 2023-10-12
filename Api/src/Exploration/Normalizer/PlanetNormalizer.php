<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Exploration\Entity\Planet;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PlanetNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Planet;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var Planet $planet */
        $planet = $object;

        return [
            'id' => $planet->getId(),
            'name' => $this->normalizer->normalize($planet->getName(), $format, $context),
            'orientation' => $this->translationService->translate(
                key: $planet->getOrientation(),
                parameters: [],
                domain: 'planet',
                language: $planet->getDaedalus()->getLanguage()
            ),
            'distance' => $planet->getDistance(),
            'sectors' => $this->normalizer->normalize($planet->getSectors()->toArray(), $format, $context),
        ];
    }
}
