<?php

namespace Mush\Player\Event;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Entity\Player;

class PlayerVariableEvent extends PlayerEvent implements VariableEventInterface
{
    private float $quantity;
    private string $variableName;

    public function __construct(
        Player $player,
        string $variableName,
        float $quantity,
        array $tags,
        \DateTime $time
    ) {
        $this->variableName = $variableName;

        parent::__construct($player, $tags, $time);
        $this->quantity = $quantity;
        $this->addTagsFromQuantity();
        $this->addTag($variableName);
    }

    public function getRoundedQuantity(): int
    {
        return (int) $this->quantity;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        // this event quantity should never change sign
        if (
            $quantity !== 0.
            && $this->quantity !== 0.
            && abs($this->quantity) / $this->quantity !== abs($quantity) / $quantity
        ) {
            $this->quantity = 0;
        } else {
            $this->quantity = $quantity;
        }

        $this->addTagsFromQuantity();

        return $this;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getVariable(): GameVariable
    {
        return $this->getPlayer()->getVariableByName($this->variableName);
    }

    public function getLogParameters(): array
    {
        $params = parent::getLogParameters();
        $params['quantity'] = abs($this->quantity);

        return $params;
    }

    private function addTagsFromQuantity(): void
    {
        if ($this->quantity < 0) {
            $this->addTag(VariableEventInterface::LOSS);
        } else {
            $key = array_search(VariableEventInterface::LOSS, $this->tags, true);
            if ($key !== false) {
                unset($this->tags[$key]);
            }
        }

        if ($this->quantity > 0) {
            $this->addTag(VariableEventInterface::GAIN);
        } else {
            $key = array_search(VariableEventInterface::GAIN, $this->tags, true);
            if ($key !== false) {
                unset($this->tags[$key]);
            }
        }
    }
}
