<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Event\ModifiableEventInterface;
use Mush\Player\Entity\Player;

class DaedalusVariableEvent extends DaedalusEvent implements VariableEventInterface, ModifiableEventInterface
{
    private int $quantity;
    private string $variableName;
    private ?Player $player = null;

    public function __construct(
        Daedalus  $daedalus,
        string    $variableName,
        int       $quantity,
        array     $tags,
        \DateTime $time
    ) {
        $this->variableName = $variableName;
        $this->quantity = $quantity;

        parent::__construct($daedalus, $tags, $time);
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getVariable(): GameVariable
    {
        return $this->daedalus->getVariableByName($this->variableName);
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getVariableHolder(): GameVariableHolderInterface
    {
        return $this->daedalus;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getModifiers(): ModifierCollection
    {
        if ($this->player !== null) {
            return $this->player->getAllModifiers();
        }

        return $this->daedalus->getAllModifiers();
    }
}
