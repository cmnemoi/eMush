<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\UseWeaponService;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Hit extends AttemptAction
{
    protected ActionEnum $name = ActionEnum::HIT;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        private UseWeaponService $useWeaponService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService
        );
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(
            new HasEquipment([
                'reach' => ReachEnum::INVENTORY,
                'equipments' => [ItemEnum::KNIFE],
                'all' => false,
                'contains' => false,
                'groups' => [ClassConstraint::VISIBILITY],
            ])
        );
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->useWeaponService->execute(
            result: $result,
            tags: $this->getTags(),
        );
    }
}
