<?php

namespace Mush\Status\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

class StatusCycleEvent extends DaedalusCycleEvent
{
    public const STATUS_NEW_CYCLE = 'status.new.cycle';
    public const STATUS_NEW_DAY = 'status.new.day';

    protected Status $status;
    protected StatusHolderInterface $holder;

    public function __construct(
        Status $status,
        StatusHolderInterface $holder,
        Daedalus $daedalus,
        string $reason,
        \DateTime $time
    ) {
        parent::__construct($daedalus, $reason, $time);

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
}
