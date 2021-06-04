<?php

namespace Mush\Status\Normalizer;

use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatusNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Status;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $status = $object;
        $statusName = $status->getName();

        if (!($currentPlayer = $context['currentPlayer'] ?? null)) {
            throw new \LogicException('Current player is missing from context');
        }

        if ($this->isVisibilityPublic($status) ||
            $this->isVisibilityPrivateForUser($status, $currentPlayer) ||
            ($status->getVisibility() === VisibilityEnum::MUSH && $currentPlayer->isMush())
        ) {
            $normedStatus = [
                'key' => $statusName,
                'name' => $this->translator->trans($statusName . '.name', [], 'status'),
                'description' => $this->translator->trans("{$statusName}.description", [], 'status'),
            ];

            if ($status instanceof ChargeStatus && $status->getChargeVisibility() !== VisibilityEnum::HIDDEN) {
                $normedStatus['charge'] = $status->getCharge();
            }

            return $normedStatus;
        }

        return [];
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
