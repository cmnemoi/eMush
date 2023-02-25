<?php

namespace Mush\Status\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

class StatusCycleEvent extends AbstractGameEvent
{
    public const STATUS_NEW_CYCLE = 'status.new.cycle';
    public const STATUS_NEW_DAY = 'status.new.day';

    protected Status $status;
    protected StatusHolderInterface $holder;

    public function __construct(
        Status $status,
        StatusHolderInterface $holder,
        array $tags,
        \DateTime $time
    ) {
        parent::__construct($tags, $time);

        $this->status = $status;
        $this->holder = $holder;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getHolder(): StatusHolderInterface
    {
        return $this->holder;
    }

    public function getModifiers(): ModifierCollection
    {
        $holder = $this->getHolder();

        if ($holder instanceof ModifierHolder) {
            return $holder->getAllModifiers()->getEventModifiers($this);
        }

        return new ModifierCollection();
    }
}
