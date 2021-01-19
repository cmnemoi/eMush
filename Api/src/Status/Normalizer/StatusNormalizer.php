<?php

namespace Mush\Status\Normalizer;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\MedicalCondition;
use Mush\Status\Entity\Status;
use Mush\User\Entity\User;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatusNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
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

        if ($this->isVisibilityPublic($status, $context) ||
            $this->isVisibilityPrivateForUser($status, $context) ||
            ($status->getVisibility() === VisibilityEnum::MUSH && $this->getUserPlayer()->isMush())
        ) {
            $normedStatus = [
                'key' => $statusName,
                'name' => $this->translator->trans($statusName . '.name', [], 'statuses'),
                'description' => $this->translator->trans("{$statusName}.description", [], 'statuses'),
            ];

            if ($status instanceof ChargeStatus && $status->getChargeVisibility() !== VisibilityEnum::HIDDEN) {
                $normedStatus['charge'] = $status->getCharge();
            }

            if ($status instanceof MedicalCondition) {
                $normedStatus['effect'] = $this->translator->trans("{$statusName}.effect", [], 'statuses');
            }

            return $normedStatus;
        }

        return [];
    }

    private function isVisibilityPublic(Status $status, array $context): bool
    {
        $visibility = $status->getVisibility();

        $playerContext = array_key_exists('player', $context) && $context['player'] instanceof Player;

        return $visibility === VisibilityEnum::PUBLIC ||
            ($visibility === VisibilityEnum::PLAYER_PUBLIC && $playerContext)
        ;
    }

    private function isVisibilityPrivateForUser(Status $status, array $context): bool
    {
        $visibility = $status->getVisibility();
        $equipmentContext = isset($context['equipment']) && $context['equipment'] instanceof GameEquipment;
        //@TODO : check that
        return true;
//        return $this->getUserPlayer() === $status->getPlayer() &&
//            ($visibility === VisibilityEnum::PRIVATE ||
//                ($visibility === VisibilityEnum::EQUIPMENT_PRIVATE && $equipmentContext)
//            )
//        ;
    }

    private function getUserPlayer(): Player
    {
        if (!$token = $this->tokenStorage->getToken()) {
            throw new AccessDeniedException('User should be logged to access that');
        }

        /** @var User $user */
        $user = $token->getUser();

        if (!$player = $user->getCurrentGame()) {
            throw new AccessDeniedException('User should be in game to access that');
        }

        return $player;
    }
}
