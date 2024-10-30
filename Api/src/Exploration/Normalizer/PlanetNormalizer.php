<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PlanetNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use ActionHolderNormalizerTrait;
    use NormalizerAwareTrait;

    private const NUMBER_OF_PLANET_IMAGES = 5;
    private GearToolServiceInterface $gearToolService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        GearToolServiceInterface $gearToolService,
        TranslationServiceInterface $translationService
    ) {
        $this->gearToolService = $gearToolService;
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Planet;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Planet::class => true,
        ];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        /** @var Planet $planet */
        $planet = $object;
        $context[$planet->getClassName()] = $planet;
        $daedalus = $planet->getDaedalus();

        // integer seed from planet name to get always the same image for the same planet
        $planetImageId = \intval(hash('crc32', $planet->getName()->toString()), 16) % self::NUMBER_OF_PLANET_IMAGES;

        // Normalize full planet only under those conditions to avoid leaking information
        // because the planet has to be normalized to be displayed in Phaser scene too
        $currentPlayerFocusedOnAstroTerminal = $currentPlayer->getFocusedTerminal()?->getName() === EquipmentEnum::ASTRO_TERMINAL;
        $currentPlayerIsExploring = $currentPlayer->isExploringOrIsLostOnPlanet();
        if (!$currentPlayerFocusedOnAstroTerminal && !$currentPlayerIsExploring) {
            return [
                'id' => $planet->getId(),
                'imageId' => $planetImageId,
            ];
        }

        return [
            'id' => $planet->getId(),
            'name' => $this->translationService->translate(
                key: 'planet_name',
                parameters: $planet->getName()->toArray(),
                domain: 'planet',
                language: $daedalus->getLanguage()
            ),
            'orientation' => $this->translationService->translate(
                key: $planet->getOrientation(),
                parameters: [],
                domain: 'planet',
                language: $daedalus->getLanguage()
            ),
            'distance' => $planet->getDistance(),
            'sectors' => $this->normalizer->normalize($planet->getSectors()->toArray(), $format, $context),
            'actions' => $this->getNormalizedActions($planet, ActionHolderEnum::PLANET, $currentPlayer, $format, $context),
            'imageId' => $planetImageId,
        ];
    }
}
