<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasRole;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Allow admins to suicide themselves.
 */
class Suicide extends AbstractAction
{
    protected string $name = ActionEnum::SUICIDE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasRole(['role' => RoleEnum::SUPER_ADMIN, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        $deathEvent = new PlayerEvent($this->player, EndCauseEnum::SUICIDE, new \DateTime());

        $this->eventDispatcher->dispatch($deathEvent, PlayerEvent::DEATH_PLAYER);

        return new Success();
    }
}
