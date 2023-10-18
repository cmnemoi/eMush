<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PlanetNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private GearToolServiceInterface $gearToolService;
    private PlanetNameNormalizer $planetNameNormalizer;
    private TranslationServiceInterface $translationService;

    public function __construct(
        GearToolServiceInterface $gearToolService,
        PlanetNameNormalizer $planetNameNormalizer,
        TranslationServiceInterface $translationService
    ) {
        $this->gearToolService = $gearToolService;
        $this->planetNameNormalizer = $planetNameNormalizer;
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
            'name' => $this->planetNameNormalizer->normalize($planet->getName()->toArray(), $format, $context),
            'orientation' => $this->translationService->translate(
                key: $planet->getOrientation(),
                parameters: [],
                domain: 'planet',
                language: $planet->getDaedalus()->getLanguage()
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
