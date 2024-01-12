<?php

namespace Mush\Status\Normalizer;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
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

        if ($this->isStatusVisibleForPlayer($status, $currentPlayer, $context)) {
            $normedStatus = [
                'key' => $statusName,
                'name' => $this->translationService->translate($statusName . '.name', [], 'status', $language),
                'description' => $this->translationService->translate("{$statusName}.description", [], 'status', $language),
                'isPrivate' => $status->getVisibility() === VisibilityEnum::PRIVATE,
            ];

            if ($status instanceof ChargeStatus && $status->getChargeVisibility() !== VisibilityEnum::HIDDEN) {
                $normedStatus['charge'] = $status->getOwner()->hasStatus(EquipmentStatusEnum::BROKEN) ? 0 : $status->getCharge();
            }

            if (($target = $status->getTarget()) !== null) {
                $normedStatus['target'] = ['key' => $target->getName(), 'id' => $target->getId()];
            }

            return $normedStatus;
        }

        return [];
    }

    private function isStatusVisibleForPlayer(Status $status, Player $currentPlayer, array $context): bool
    {
        $isAdmin = isset($context['groups']) && in_array('admin_view', $context['groups'], true);
        $isVisibleMush = $status->getVisibility() === VisibilityEnum::MUSH && $currentPlayer->isMush();

        return $isAdmin
        || $isVisibleMush
        || $this->isVisibilityPublic($status)
        || $this->isVisibilityPrivateForUser($status, $currentPlayer);
    }

    private function isVisibilityPublic(Status $status): bool
    {
        $visibility = $status->getVisibility();

        return $visibility === VisibilityEnum::PUBLIC;
    }

    private function isVisibilityPrivateForUser(Status $status, Player $currentPlayer): bool
    {
        $visibility = $status->getVisibility();

        if (($owner = $status->getOwner()) instanceof Player) {
            $player = $owner;
        } elseif (($target = $status->getTarget()) instanceof Player) {
            $player = $target;
        } else {
            return false;
        }

        return $visibility === VisibilityEnum::PRIVATE && $player === $currentPlayer;
    }
}
