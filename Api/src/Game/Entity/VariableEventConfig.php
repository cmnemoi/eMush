<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

/**
 * Class storing the various information needed to create a variableEvent.
 *
 * name: a unique name needed for the DB
 * targetVariable: the name of the game Variable modified by the event
 * variableHolderClass: the name of the class on which the event will be applied (should be a variableHolderInterface)
 * mode: what pat of the game variable is modified (value, max or min)
 * quantity: the amount of point modified
 */
#[ORM\Entity]
class VariableEventConfig extends AbstractEventConfig
{
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $quantity = 0;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $targetVariable;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $variableHolderClass;

    public function buildName(): static
    {
        $this->name = $this->eventName . '_' . $this->variableHolderClass . '_' . $this->quantity . '_' . $this->targetVariable;

        return $this;
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

    public function getTargetVariable(): string
    {
        return $this->targetVariable;
    }

    public function setTargetVariable(string $targetVariable): self
    {
        $this->targetVariable = $targetVariable;

        return $this;
    }

    public function getVariableHolderClass(): string
    {
        return $this->variableHolderClass;
    }

    public function setVariableHolderClass(string $variableHolderClass): self
    {
        $this->variableHolderClass = $variableHolderClass;

        return $this;
    }

    public function createEvent(
        int $priority,
        array $tags,
        \DateTime $date,
        GameVariableHolderInterface $variableHolder = null
    ): AbstractGameEvent {
        switch ($this->variableHolderClass) {
            case ModifierHolderClassEnum::PLAYER:
                if (!$variableHolder instanceof Player) {
                    throw new \Exception('a player should be provided to create a playerVariableEvent');
                }
                $event = new PlayerVariableEvent($variableHolder, $this->targetVariable, $this->quantity, $tags, $date);
                $event->setEventName($this->eventName)->setPriority($priority);

                return $event;
            case ModifierHolderClassEnum::DAEDALUS:
                if (!$variableHolder instanceof Daedalus) {
                    throw new \Exception('a daedalus should be provided to create a daedalusVariableEvent');
                }
                $event = new DaedalusVariableEvent($variableHolder, $this->targetVariable, $this->quantity, $tags, $date);
                $event->setEventName($this->eventName)->setPriority($priority);

                return $event;
            default:
                throw new \Exception("unexpected variableClassHolder: {$this->variableHolderClass}");
        }
    }

    public function revertEvent(): ?AbstractEventConfig
    {
        $reverseEvent = new VariableEventConfig();
        $reverseEvent
            ->setTargetVariable($this->targetVariable)
            ->setVariableHolderClass($this->variableHolderClass)
            ->setQuantity(-$this->quantity)
            ->setEventName($this->eventName)
        ;

        return $reverseEvent;
    }

    public function getTranslationKey(): ?string
    {
        if ($this->quantity < 0) {
            return $this->eventName . '.decrease';
        } else {
            return $this->eventName . '.increase';
        }
    }

    public function getTranslationParameters(): array
    {
        $parameters = [
            'quantity' => abs($this->quantity),
            'target_variable' => $this->targetVariable,
        ];

        $emoteMap = PlayerVariableEnum::getEmoteMap();
        if (isset($emoteMap[$this->targetVariable])) {
            $parameters['emote'] = $emoteMap[$this->targetVariable];
        }

        return $parameters;
    }
}
