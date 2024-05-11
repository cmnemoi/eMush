<?php

namespace Mush\Status\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

class StatusCycleEvent extends AbstractGameEvent
{
    public const STATUS_NEW_CYCLE = 'status.new.cycle';

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

    public function getDaedalus(): Daedalus
    {
        return $this->holder->getDaedalus();
    }

    public function getModifiersByPriorities(array $priorities): ModifierCollection
    {
        $holder = $this->getHolder();

        if ($holder instanceof ModifierHolderInterface) {
            return $holder->getAllModifiers()->getEventModifiers($this, $priorities);
        }

        return new ModifierCollection();
    }
}
