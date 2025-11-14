<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerHighlightSourceEventInterface;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\Player\ValueObject\PlayerHighlightTargetInterface;
use Mush\RoomLog\Event\LoggableEventInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Triumph\Enum\TriumphTarget;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

class EquipmentEvent extends EquipmentCycleEvent implements LoggableEventInterface, TriumphSourceEventInterface, PlayerHighlightSourceEventInterface
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

    public function getHighlightName(): string
    {
        return $this->getEventName() . '_' . $this->getGameEquipment()->getLogName();
    }

    public function getHighlightResult(): string
    {
        return PlayerHighlight::SUCCESS;
    }

    public function getHighlightTarget(): PlayerHighlightTargetInterface
    {
        return $this->getGameEquipment();
    }

    public function hasHighlightTarget(): bool
    {
        return true;
    }

    public function getPlayerHolderOrThrow(): Player
    {
        $holder = $this->getGameEquipment()->getHolder();
        if (!$holder instanceof Player) {
            throw new \LogicException('Equipment holder is not a player');
        }

        return $holder;
    }

    protected function getEventSpecificTargets(TriumphTarget $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        return match ($targetSetting) {
            TriumphTarget::AUTHOR => $scopeTargets->filter(fn (Player $player) => $player === $this->getAuthor()),
            TriumphTarget::ACTIVE_EXPLORERS => $scopeTargets->filter(fn (Player $player) => $this->daedalus->getExplorationOrThrow()->getNotLostActiveExplorators()->contains($player)),
            default => throw new \LogicException("Triumph target {$targetSetting->toString()} is not supported"),
        };
    }
}
