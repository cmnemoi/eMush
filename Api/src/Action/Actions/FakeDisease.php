<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;

/**
 * Class implementing the "Fake disease" action.
 *
 * For 1 PA, "Fake disease" gives current player a disease.
 * This action is implemented for test purpose but may be further used as a mush skill
 */
class FakeDisease extends AbstractAction
{
    protected string $name = ActionEnum::FAKE_DISEASE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    protected function applyEffects(): ActionResult
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $this->player,
            VisibilityEnum::PUBLIC,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);

        return new Success();
    }
}
