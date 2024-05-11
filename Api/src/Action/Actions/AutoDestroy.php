<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasRole;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Allow admins to finish a Daedalus.
 */
class AutoDestroy extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::AUTO_DESTROY;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasRole(['roles' => [RoleEnum::SUPER_ADMIN], 'groups' => ['visibility']]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $deathEvent = new DaedalusEvent($this->player->getDaedalus(), $this->getActionConfig()->getActionTags(), new \DateTime());

        $this->eventService->callEvent($deathEvent, DaedalusEvent::FINISH_DAEDALUS);
    }
}
