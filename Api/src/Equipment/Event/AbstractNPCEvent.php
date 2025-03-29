<?php

declare(strict_types=1);

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Event\LoggableEventInterface;

abstract class AbstractNPCEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public function __construct(
        protected GameEquipment $NPC,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ) {
        parent::__construct($tags, $time);
    }

    public function getNPC(): GameEquipment
    {
        return $this->NPC;
    }

    public function getPlace(): Place
    {
        return $this->NPC->getPlace();
    }

    public function getVisibility(): string
    {
        return VisibilityEnum::PUBLIC;
    }

    public function getLogParameters(): array
    {
        return [
            $this->NPC->getLogKey() => $this->NPC->getLogName(),
        ];
    }

    public function getDaedalusLanguage(): string
    {
        return $this->NPC->getDaedalus()->getLanguage();
    }
}
