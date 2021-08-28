<?php

namespace Mush\Status\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractLoggedEvent;
use Mush\Game\Event\AbstractMushEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\StatusHolderInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusEvent extends AbstractMushEvent implements AbstractLoggedEvent
{
    public const STATUS_APPLIED = 'status.applied';
    public const STATUS_REMOVED = 'status.removed';

    private string $statusName;
    private StatusHolderInterface $holder;
    private ?StatusHolderInterface $target;

    private string $visibility = VisibilityEnum::HIDDEN;

    public function __construct(
        string $statusName,
        StatusHolderInterface $holder,
        string $reason,
        \DateTime $time
    ) {
        $this->statusName = $statusName;
        $this->holder = $holder;
        parent::__construct($reason, $time);
    }

    public function getStatusName(): string
    {
        return $this->statusName;
    }

    public function getStatusHolder(): StatusHolderInterface
    {
        return $this->holder;
    }

    public function getStatusTarget(): ?StatusHolderInterface
    {
        return $this->target;
    }

    public function setStatusTarget(StatusHolderInterface $target): StatusEvent
    {
        $this->target = $target;

        return $this;
    }

    public function setVisibility(string $visibility): StatusEvent
    {
        $this->visibility = $visibility;

        return $this;
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
