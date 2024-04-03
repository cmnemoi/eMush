<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PlanetSectorNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof PlanetSector;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var PlanetSector $planetSector */
        $planetSector = $object;

        $key = $planetSector->isRevealed() ? $planetSector->getName() : PlanetSectorEnum::UNKNOWN;

        return [
            'id' => $planetSector->getId(),
            'key' => $key,
            'name' => $this->translationService->translate(
                $key . '.name',
                parameters: [],
                domain: 'planet',
                language: $planetSector->getPlanet()->getDaedalus()->getLanguage()
            ),
            'description' => $this->translationService->translate(
                $key . '.description',
                parameters: [],
                domain: 'planet',
                language: $planetSector->getPlanet()->getDaedalus()->getLanguage()
            ),
            'isVisited' => $planetSector->isVisited(),
            'isRevealed' => $planetSector->isRevealed(),
        ];
    }
}
