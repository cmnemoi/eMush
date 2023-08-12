<?php

declare(strict_types=1);

namespace Mush\Hunter\Normalizer;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\HunterStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class HunterNormalizer implements NormalizerInterface, NormalizerAwareInterface
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
        return $data instanceof Hunter && !$data->isInPool();
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        /** @var Hunter $hunter */
        $hunter = $object;
        /** @var ChargeStatus $hunterCharges */
        $hunterCharges = $hunter->getStatusByName(HunterStatusEnum::HUNTER_CHARGE);
        // if hunter (not asteroid) is not in truce cycle anymore, it may not have charges
        $hunterChargesAmount = $hunterCharges?->getCharge();
        $hunterHealth = $hunter->getHealth();
        $hunterKey = $hunter->getName();
        $isHunterAnAsteroid = $hunterKey === HunterEnum::ASTEROID;

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
                    'charges' => $hunterChargesAmount ?? 0,
                    'health' => $hunterHealth,
                ],
                domain: 'hunter',
                language: $hunter->getDaedalus()->getLanguage()
            ),
            'health' => $hunterHealth,
            'charges' => $isHunterAnAsteroid ? null : $hunterChargesAmount,
            'actions' => $this->gearToolService->getActionsTools(
                player: $currentPlayer,
                scopes: [ActionScopeEnum::ROOM],
                target: Hunter::class
            ),
        ];
    }
}
