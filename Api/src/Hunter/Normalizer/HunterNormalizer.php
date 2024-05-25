<?php

declare(strict_types=1);

namespace Mush\Hunter\Normalizer;

use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
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

    public function __construct(
        private TranslationServiceInterface $translationService
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Hunter && !$data->isInPool();
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        /** @var Hunter $hunter */
        $hunter = $object;
        $context[$hunter->getClassName()] = $hunter;

        /** @var ChargeStatus $asteroidTruceCyclesStatus */
        $asteroidTruceCyclesStatus = $hunter->getStatusByName(HunterStatusEnum::ASTEROID_TRUCE_CYCLES);
        $asteroidTruceCycles = $asteroidTruceCyclesStatus?->getCharge();

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
                    'charges' => $asteroidTruceCycles ?? 0,
                    'health' => $hunterHealth,
                ],
                domain: 'hunter',
                language: $hunter->getDaedalus()->getLanguage()
            ),
            'health' => $hunterHealth,
            'charges' => $asteroidTruceCycles,
            'actions' => $this->getNormalizedActions($hunter, ActionHolderEnum::HUNTER, $currentPlayer, $format, $context),
        ];
    }
}
