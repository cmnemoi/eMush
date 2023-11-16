<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Action\Enum\ActionScopeEnum;
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
    use NormalizerAwareTrait;

    private GearToolServiceInterface $gearToolService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        GearToolServiceInterface $gearToolService,
        TranslationServiceInterface $translationService
    ) {
        $this->gearToolService = $gearToolService;
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Planet;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        /** @var Planet $planet */
        $planet = $object;
        $daedalus = $planet->getDaedalus();

        // Do not leak planet sections if player is not focused on astro terminal or in exploration (TODO)
        // as the planet is also normalized to display it in Phaser scene
        if ($currentPlayer->getFocusedTerminal()?->getName() !== EquipmentEnum::ASTRO_TERMINAL) {
            return [
                'id' => $planet->getId(),
                'name' => $this->translationService->translate(
                    key: 'planet_name',
                    parameters: $planet->getName()->toArray(),
                    domain: 'planet',
                    language: $daedalus->getLanguage()
                ),
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
            'actions' => $this->normalizePlanetActions($planet, $format, $context),
        ];
    }

    private function normalizePlanetActions(Planet $planet, string $format = null, array $context = []): array
    {
        $actions = [];
        $currentPlayer = $context['currentPlayer'];
        $context['planet'] = $planet;

        $toolsActions = $this->gearToolService->getActionsTools(
            player: $currentPlayer,
            scopes: [ActionScopeEnum::TERMINAL],
            target: Planet::class,
        );

        foreach ($toolsActions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        return $actions;
    }
}
