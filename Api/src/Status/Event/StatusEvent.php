<?php

namespace Mush\Status\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Event\LoggableEventInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\StatusHolderInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public const STATUS_APPLIED = 'status.applied';
    public const STATUS_REMOVED = 'status.removed';

    protected string $statusName;
    protected ?StatusConfig $statusConfig = null;
    protected StatusHolderInterface $holder;
    protected ?StatusHolderInterface $target = null;

    protected string $visibility = VisibilityEnum::HIDDEN;

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

    public function getStatusConfig(): ?StatusConfig
    {
        return $this->statusConfig;
    }

    public function setStatusConfig(StatusConfig $statusConfig): self
    {
        $this->statusConfig = $statusConfig;

        return $this;
    }

    public function getStatusTarget(): ?StatusHolderInterface
    {
        return $this->target;
    }

    public function setStatusTarget(StatusHolderInterface $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function setVisibility(string $visibility): self
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
            return $this->holder->getPlace();
        } elseif ($this->holder instanceof Player) {
            return $this->holder->getPlace();
        } else {
            throw new UnexpectedTypeException($this->holder, StatusHolderInterface::class);
        }
    }

    public function getLogParameters(): array
    {
        $parameters = [];
        if ($this->holder instanceof Player || $this->holder instanceof GameEquipment){
            $parameters[$this->holder->getLogKey()] = $this->holder->getLogName();
        }

        if ($this->target instanceof Player || $this->target instanceof GameEquipment){
            $parameters['target_'.$this->target->getLogKey()] = $this->target->getLogName();
        }

        return $parameters;
    }
}
