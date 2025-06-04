<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Triumph\Enum\TriumphTarget;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

class EquipmentEvent extends EquipmentCycleEvent implements LoggableEventInterface, TriumphSourceEventInterface
{
    use TriumphSourceEventTrait;

    public const EQUIPMENT_BROKEN = 'equipment.broken';
    public const EQUIPMENT_CREATED = 'equipment.created';
    public const EQUIPMENT_DESTROYED = 'equipment.destroyed';
    public const EQUIPMENT_DELETE = 'equipment.delete';
    public const EQUIPMENT_TRANSFORM = 'equipment.transform';
    public const DOOR_BROKEN = 'door.broken';
    public const INVENTORY_OVERFLOW = 'inventory.overflow';
    public const CHANGE_HOLDER = 'change.holder';

    private string $visibility;
    private bool $created;

    public function __construct(
        GameEquipment $equipment,
        bool $created,
        string $visibility,
        array $tags,
        \DateTime $time
    ) {
        $this->visibility = $visibility;
        $this->created = $created;

        parent::__construct($equipment, $equipment->getDaedalus(), $tags, $time);
        $this->addTag($equipment->getName());
        if ($equipment->getName() === ItemEnum::STARMAP_FRAGMENT
        && $this->daedalus->doesNotHaveStatus(DaedalusStatusEnum::FIRST_STARMAP_FRAGMENT)) {
            $this->addTag(DaedalusStatusEnum::FIRST_STARMAP_FRAGMENT);
        }
    }

    public function isCreated(): bool
    {
        return $this->created;
    }

    public function getPlace(): Place
    {
        return $this->gameEquipment->getPlace();
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getLogParameters(): array
    {
        $logParameters = [];

        if ($this->created) {
            $logParameters['target_' . $this->gameEquipment->getLogKey()] = $this->gameEquipment->getLogName();
        } else {
            $logParameters[$this->gameEquipment->getLogKey()] = $this->gameEquipment->getLogName();
        }

        $holder = $this->gameEquipment->getHolder();
        if ($holder instanceof Player) {
            $logParameters[$holder->getLogKey()] = $holder->getLogName();
        }

        return $logParameters;
    }

    protected function getEventSpecificTargets(TriumphTarget $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        return match ($targetSetting) {
            TriumphTarget::ACTIVE_EXPLORERS => $scopeTargets->filter(fn (Player $player) => $this->daedalus->getExplorationOrThrow()->getNotLostActiveExplorators()->contains($player)),
            default => throw new \LogicException("Triumph target {$targetSetting->toString()} is not supported"),
        };
    }
}
