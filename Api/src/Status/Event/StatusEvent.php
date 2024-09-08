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
    public const string STATUS_APPLIED = 'status.applied';
    public const string STATUS_DELETED = 'status.deleted';
    public const string STATUS_REMOVED = 'status.removed';

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
        ?StatusHolderInterface $target = null
    ) {
        $this->status = $status;
        $this->holder = $holder;
        $this->daedalus = $holder->getDaedalus();
        $this->target = $target;

        parent::__construct($tags, $time);
        $this->addTag($status->getName());
        $this->addTag($holder->getName());
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

    public function getGameEquipmentStatusHolder(): GameEquipment
    {
        return $this->holder instanceof GameEquipment ? $this->holder : throw new \RuntimeException('The holder of this status is not a GameEquipment');
    }

    public function getPlayerStatusHolder(): Player
    {
        return $this->holder instanceof Player ? $this->holder : throw new \RuntimeException('The holder of this status is not a Player');
    }

    public function getStatusConfig(): StatusConfig
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
        }
        if ($this->holder instanceof Player) {
            return $this->holder->getPlace();
        }
        if ($this->holder instanceof GameEquipment) {
            return $this->holder->getPlace();
        }

        return null;
    }

    public function getPlaceOrThrow(): Place
    {
        return $this->getPlace() ?? throw new \LogicException('This status event does not have a place');
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

    public function getModifiersByPriorities(array $priorities): ModifierCollection
    {
        $holder = $this->holder;

        if ($holder instanceof ModifierHolderInterface) {
            return $holder->getAllModifiers()->getEventModifiers($this, $priorities);
        }

        return new ModifierCollection();
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
