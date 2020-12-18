<?php

namespace Mush\Status\Normalizer;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\MedicalCondition;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
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
        $visibility = $status->getVisibility();

        if ($visibility === VisibilityEnum::PUBLIC ||
           ($visibility === VisibilityEnum::PLAYER_PUBLIC &&
           array_key_exists('player', $context) && $context['player'] instanceof Player) ||
           ((($visibility === VisibilityEnum::EQUIPMENT_PRIVATE &&
           array_key_exists('equipment', $context) && $context['equipment'] instanceof GameEquipment) ||
           $visibility === VisibilityEnum::PRIVATE) &&
           $this->getUser()->getCurrentGame() === $status->getPlayer()) ||
           ($visibility === VisibilityEnum::MUSH &&
           $this->getUser()->getCurrentGame()->isMush())
           ) {
            $normedStatus = [
                'key' => $statusName,
                'name' => $this->translator->trans($statusName . '.name', [], 'statuses'),
                'description' => $this->translator->trans("{$statusName}.description", [], 'statusess'),
            ];

            if ($status instanceof ChargeStatus) {
                $normedStatus['charge'] = $status->getCharge();
            }

            if ($status instanceof MedicalCondition) {
                $normedStatus['effect'] = $this->translator->trans("{$statusName}.effect", [], 'statuses');
            }

            return $normedStatus;
        }

        return [];
    }

    private function getUser(): User
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $user;
    }
}
