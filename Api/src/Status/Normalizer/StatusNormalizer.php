<?php

namespace Mush\Status\Normalizer;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class StatusNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Status;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $status = $object;
        $statusName = $status->getName();

        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        $language = $currentPlayer->getDaedalus()->getLanguage();

        if ($this->isVisible($status->getVisibility(), $currentPlayer, $status->getOwner(), $status->getTarget())) {
            $normedStatus = [
                'key' => $statusName,
                'name' => $this->translationService->translate($statusName . '.name', [], 'status', $language),
                'description' => $this->translationService->translate("{$statusName}.description", [], 'status', $language),
                'isPrivate' => $status->getVisibility() === VisibilityEnum::PRIVATE,
            ];

            if (
                $status instanceof ChargeStatus
                && $this->isVisible($status->getChargeVisibility(), $currentPlayer, $status->getOwner(), $status->getTarget())
            ) {
                $normedStatus['charge'] = $status->getOwner()->hasStatus(EquipmentStatusEnum::BROKEN) ? 0 : $status->getCharge();
            }

            if (($target = $status->getTarget()) !== null) {
                $normedStatus['target'] = ['key' => $target->getName(), 'id' => $target->getId()];
            }

            return $normedStatus;
        }

        return [];
    }

    private function isVisible(
        string $visibility,
        Player $currentPlayer,
        ?StatusHolderInterface $statusOwner,
        ?StatusHolderInterface $statusTarget,
    ): bool {
        if ($visibility === VisibilityEnum::PUBLIC) {
            return true;
        }

        if ($visibility === VisibilityEnum::MUSH && $currentPlayer->isMush()) {
            return true;
        }

        if ($statusOwner instanceof Player) {
            $player = $statusOwner;
        } elseif ($statusTarget instanceof Player) {
            $player = $statusTarget;
        } else {
            return false;
        }

        return $visibility === VisibilityEnum::PRIVATE && $player === $currentPlayer;
    }
}
