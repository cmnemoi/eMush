<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasRole;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Allow admins to finish a Daedalus.
 */
class AutoDestroy extends AbstractAction
{
    protected string $name = ActionEnum::AUTO_DESTROY;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasRole(['roles' => [RoleEnum::SUPER_ADMIN, RoleEnum::ADMIN], 'groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $deathEvent = new DaedalusEvent($this->player->getDaedalus(), EndCauseEnum::SUPER_NOVA, new \DateTime());

        $this->eventDispatcher->dispatch($deathEvent, DaedalusEvent::FINISH_DAEDALUS);
    }
}
