<?php

declare(strict_types=1);

namespace Mush\Hunter\Normalizer;

use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\HunterStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class HunterNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use ActionHolderNormalizerTrait;
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
        return $data instanceof Hunter && !$data->isInPool();
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        /** @var Hunter $hunter */
        $hunter = $object;
        $context['hunter'] = $hunter;

        /** @var ChargeStatus $hunterTruceCyclesStatus */
        $hunterTruceCyclesStatus = $hunter->getStatusByName(HunterStatusEnum::TRUCE_CYCLES);
        $hunterTruceCycles = $hunterTruceCyclesStatus?->getCharge();  // only asteroids have truce cycles

        $hunterHealth = $hunter->getHealth();
        $hunterKey = $hunter->getName();

        return [
            'id' => $hunter->getId(),
            'key' => $hunterKey,
            'name' => $this->translationService->translate(
                key: $hunterKey . '.name',
                parameters: [],
                domain: 'hunter',
                language: $hunter->getDaedalus()->getLanguage()
            ),
            'description' => $this->translationService->translate(
                key: $hunterKey . '.description',
                parameters: [
                    'charges' => $hunterTruceCycles ?? 0,
                    'health' => $hunterHealth,
                ],
                domain: 'hunter',
                language: $hunter->getDaedalus()->getLanguage()
            ),
            'health' => $hunterHealth,
            'charges' => $hunterTruceCycles,
            'actions' => $this->getActions($currentPlayer, $format, $context),
        ];
    }

    private function getActions(Player $currentPlayer, ?string $format, array $context): array
    {
        $actions = [];

        $toolsActions = $this->gearToolService->getActionsTools(
            player: $currentPlayer,
            scopes: [ActionScopeEnum::ROOM],
            target: Hunter::class
        );

        foreach ($toolsActions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        $actions = $this->getNormalizedActionsSortedBy('name', $actions);
        $actions = $this->getNormalizedActionsSortedBy('actionPointCost', $actions);

        return $actions;
    }
}
