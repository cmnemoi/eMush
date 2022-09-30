<?php

namespace Mush\Modifier\Entity\Config\Prevent;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Status\Event\PreventStatusEvent;
use Mush\Status\Event\StatusEvent;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PreventApplyStatusModifierConfig extends ModifierConfig {

    private string $statusName;

    public function __construct(string $name, string $reach, string $statusName)
    {
        parent::__construct($name, $reach);
        $this->statusName = $statusName;
    }

    public function modify(AbstractModifierHolderEvent $event, EventServiceInterface $eventService)
    {
        if ($event instanceof StatusEvent && $this->canPrevent($event)) {
            $event->stopPropagation();

            $preventEvent = new PreventStatusEvent(
                $event->getStatusName(),
                $event->getModifierHolder(),
                $this->getName(),
                $event->getTime()
            );
            $eventService->callEvent($preventEvent, PreventStatusEvent::PREVENT_STATUS);
        }
    }

    private function canPrevent(StatusEvent $event) : bool {
        return $event->getStatusName() === $this->statusName &&
            $event->getEventName() === StatusEvent::STATUS_APPLIED;
    }

}