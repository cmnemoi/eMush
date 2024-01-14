<?php

namespace Mush\Status\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

class StatusEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public const STATUS_APPLIED = 'status.applied';
    public const STATUS_REMOVED = 'status.removed';

    protected Status $status;
    protected StatusHolderInterface $holder;
    protected ?StatusHolderInterface $target = null;
    protected Daedalus $daedalus;

    protected string $visibility = VisibilityEnum::HIDDEN;

    public function __construct(
        Status $status,
        StatusHolderInterface $holder,
        array $tags,
        \DateTime $time,
        StatusHolderInterface $target = null
    ) {
        $this->status = $status;
        $this->holder = $holder;
        $this->daedalus = $holder->getDaedalus();
        $this->target = $target;

        $tags[] = $status->getName();
        parent::__construct($tags, $time);
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getStatusName(): string
    {
        return $this->status->getName();
    }

    public function getStatusHolder(): StatusHolderInterface
    {
        return $this->holder;
    }

    public function getStatusConfig(): ?StatusConfig
    {
        return $this->status->getStatusConfig();
    }

    public function getStatusTarget(): ?StatusHolderInterface
    {
        return $this->target;
    }

    public function setStatusTarget(?StatusHolderInterface $target): self
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

    public function getPlace(): ?Place
    {
        if ($this->holder instanceof Place) {
            return $this->holder;
        } elseif ($this->holder instanceof Player) {
            return $this->holder->getPlace();
        } elseif ($this->holder instanceof GameEquipment) {
            return $this->holder->getPlace();
        }

        return null;
    }

    public function getLogParameters(): array
    {
        $parameters = [];
        if ($this->holder instanceof Player || $this->holder instanceof GameEquipment) {
            $parameters[$this->holder->getLogKey()] = $this->holder->getLogName();
        }

        if ($this->target instanceof Player || $this->target instanceof GameEquipment) {
            $parameters['target_' . $this->target->getLogKey()] = $this->target->getLogName();
        }

        return $parameters;
    }

    public function getModifiers(): ModifierCollection
    {
        $holder = $this->holder;

        if ($holder instanceof ModifierHolderInterface) {
            return $holder->getAllModifiers()->getEventModifiers($this);
        }

        return new ModifierCollection();
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
