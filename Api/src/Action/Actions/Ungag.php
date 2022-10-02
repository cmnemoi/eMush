<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasStatus as StatusValidator;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * implement ungag action.
 * For 1 Action Points, a player with gag status can ungag
 *  - remove gagged status of the current player.
 *
 * More info: http://mushpedia.com/wiki/Duct_Tape
 */
class Ungag extends AbstractAction
{
    protected string $name = ActionEnum::UNGAG;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new StatusValidator([
            'status' => PlayerStatusEnum::GAGGED,
            'target' => StatusValidator::PLAYER,
            'contain' => true,
            'groups' => ['visibility'],
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        $statusEvent = new StatusEvent(PlayerStatusEnum::GAGGED, $this->player, $this->getActionName(), new \DateTime());

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_REMOVED);

        return new Success();
    }
}
