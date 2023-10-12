<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Exploration\Entity\PlanetName;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PlanetNameNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof PlanetName;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): string
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        /** @var PlanetName $planetName */
        $planetName = $object;

        return $this->getTranslatedPlanetName($planetName, $currentPlayer);
    }

    private function getTranslatedPlanetName(PlanetName $planetName, Player $currentPlayer): string
    {
        $translatedPlanetName = '';
        foreach ($planetName->getNameAsArray() as $key => $namePart) {
            if ($namePart === null) {
                continue;
            }

            $translatedNamePart = $this->translationService->translate(
                key: 'planet_name.' . $key,
                parameters: ['version' => $namePart],
                domain: 'planet',
                language: $currentPlayer->getDaedalus()->getLanguage()
            );

            if ($key == PlanetName::PREFIX) {
                $translatedPlanetName .= $translatedNamePart . ' ';
            } elseif ($key == PlanetName::FIFTH_SYLLABLE) {
                $translatedPlanetName .= ' ' . $translatedNamePart;
            } else {
                $translatedPlanetName .= $translatedNamePart;
            }
        }

        return $translatedPlanetName;
    }
}
