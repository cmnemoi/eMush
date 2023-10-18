<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Exploration\Entity\PlanetName;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PlanetNameNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        // PlanetName entity is sent to normalizer as an array with a 'type' field

        $dataHasTypeField = is_array($data) && array_key_exists('type', $data);
        $dataTypeFieldIsPlanetName = $dataHasTypeField && $data['type'] === PlanetName::class;

        return $dataTypeFieldIsPlanetName;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): string
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        /** @var array $planetNameArray */
        $planetNameArray = $object instanceof PlanetName ? $object->toArray() : $object;

        return $this->getTranslatedPlanetName($planetNameArray, $currentPlayer);
    }

    private function getTranslatedPlanetName(array $planetNameArray, Player $currentPlayer): string
    {
        $translatedPlanetName = '';
        foreach ($planetNameArray as $key => $namePart) {
            if ($namePart === null || $key === 'type') {
                continue;
            }

            $translatedNamePart = $this->translationService->translate(
                key: 'planet_name.' . $key,
                parameters: ['version' => $namePart],
                domain: 'planet',
                language: $currentPlayer->getDaedalus()->getLanguage()
            );

            if ($key == PlanetName::FIFTH_SYLLABLE) {
                $translatedPlanetName .= ' ' . $translatedNamePart;
            } elseif ($key == PlanetName::PREFIX) {
                $translatedPlanetName .= $translatedNamePart . ' ';
            } else {
                $translatedPlanetName .= $translatedNamePart;
            }
        }

        return $translatedPlanetName;
    }
}
