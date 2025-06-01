<?php

declare(strict_types=1);

namespace Mush\Triumph\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;

final class TriumphChangedEvent extends AbstractGameEvent
{
    public function __construct(
        private Player $player,
        private TriumphConfig $triumphConfig,
        private int $quantity,
        protected array $tags = [],
        protected \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getPlace(): Place
    {
        return $this->player->getPlace();
    }

    public function getLogKey(): string
    {
        return $this->getTriumphLogName()->toString();
    }

    public function getVisibility(): string
    {
        return $this->triumphConfig->getVisibility()->toString();
    }

    public function getQuantity(): int
    {
        return abs($this->quantity);
    }

    private function getTriumphLogName(): TriumphEnum
    {
        $configName = $this->triumphConfig->getName();

        return match ($configName) {
            TriumphEnum::MUSHICIDE_CAT => TriumphEnum::MUSHICIDE,
            TriumphEnum::HUMANOCIDE_CAT => TriumphEnum::HUMANOCIDE,
            TriumphEnum::PSYCHOCAT => TriumphEnum::PSYCHOPAT,
            default => $configName,
        };
    }
}
