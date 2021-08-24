<?php

namespace Mush\Status\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractLoggedEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusEvent extends StatusCycleEvent implements AbstractLoggedEvent
{
    public const STATUS_APPLIED = 'status.applied';
    public const STATUS_REMOVED = 'status.removed';

    private string $visibility;

    public function __construct(
        Status $status,
        StatusHolderInterface $holder,
        string $visibility,
        Daedalus $daedalus,
        string $reason,
        \DateTime $time
    ) {
        $this->visibility = $visibility;

        parent::__construct($status, $holder, $daedalus, $reason, $time);
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getPlace(): Place
    {
        if ($this->holder instanceof Place) {
            return $this->holder;
        } elseif ($this->holder instanceof GameEquipment) {
            return $this->holder->getCurrentPlace();
        } elseif ($this->holder instanceof Player) {
            return $this->holder->getPlace();
        } else {
            throw new UnexpectedTypeException($this->holder, StatusHolderInterface::class);
        }
    }
}
