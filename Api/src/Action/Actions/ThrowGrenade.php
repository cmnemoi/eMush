<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\GrenadeInhibit;
use Mush\Action\Validator\NumberPlayersAliveInRoom;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\UseWeaponService;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ThrowGrenade extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::THROW_GRENADE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RandomServiceInterface $randomService,
        private UseWeaponService $useWeaponService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach([
                'reach' => ReachEnum::ROOM,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new NumberPlayersAliveInRoom([
                'mode' => NumberPlayersAliveInRoom::EQUAL,
                'number' => 1,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::LAUNCH_GRENADE_ALONE,
            ]),
            new GrenadeInhibit([
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::DMZ_CORE_PEACE,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->useWeaponService->executeWithoutTarget(
            result: $result,
            tags: $this->getTags(),
        );
    }
}
