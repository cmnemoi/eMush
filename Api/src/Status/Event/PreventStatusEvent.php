<?php

namespace Mush\Status\Event;

use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Modifier\Entity\ModifierHolder;

class PreventStatusEvent extends AbstractModifierHolderEvent {

    public const PREVENT_STATUS = 'prevent_status_event';

    private string $statusName;

    public function __construct(string $statusName, ModifierHolder $modifierHolder, string $reason, \DateTime $date)
    {
        parent::__construct($modifierHolder, $reason, $date);
        $this->statusName = $statusName;
    }

    public function getStatusName(): string
    {
        return $this->statusName;
    }

}