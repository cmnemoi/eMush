<?php

declare(strict_types=1);

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
        $this->quantity = $this->hasSignChanged($quantity) ? 0 : $quantity;

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
        $params['quantity'] = abs($this->getRoundedQuantity());

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

    private function hasSignChanged(float $newQuantity): bool
    {
        if ($newQuantity === 0. || $this->quantity === 0.) {
            return true;
        }

        return $this->sign($newQuantity) !== $this->sign($this->quantity);
    }

    private function sign(float $number): int
    {
        return $number <=> 0;
    }
}
