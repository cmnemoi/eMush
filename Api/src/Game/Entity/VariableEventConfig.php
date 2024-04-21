<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Project\Entity\Project;
use Mush\Project\Event\ProjectEfficiencyVariableEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Event\ChargeStatusEvent;

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

    /**
     * @throws \Exception
     */
    public function createEvent(
        int $priority,
        array $tags,
        \DateTime $date,
        ModifierHolderInterface $variableHolder
    ): ?AbstractGameEvent {
        $event = match ($this->variableHolderClass) {
            ModifierHolderClassEnum::PLAYER => $this->createPlayerVariableEvent($tags, $date, $variableHolder),
            ModifierHolderClassEnum::DAEDALUS => $this->createDaedalusVariableEvent($tags, $date, $variableHolder),
            ModifierHolderClassEnum::EQUIPMENT => $this->createEquipmentVariableEVent($tags, $date, $variableHolder),
            ModifierHolderClassEnum::PROJECT => $this->createProjectVariableEvent($tags, $date, $variableHolder),
            default => throw new \Exception("unexpected variableClassHolder: {$this->variableHolderClass}"),
        };

        return $event?->setEventName($this->eventName)?->setPriority($priority);
    }

    public function revertEvent(): ?AbstractEventConfig
    {
        $reverseEvent = new self();
        $reverseEvent
            ->setTargetVariable($this->targetVariable)
            ->setVariableHolderClass($this->variableHolderClass)
            ->setQuantity(-$this->quantity)
            ->setEventName($this->eventName);

        return $reverseEvent;
    }

    public function getTranslationKey(): ?string
    {
        if ($this->quantity < 0) {
            return $this->eventName . '.decrease';
        }

        return $this->eventName . '.increase';
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

    private function createDaedalusVariableEvent(
        array $tags,
        \DateTime $date,
        ModifierHolderInterface $variableHolder
    ): ?AbstractGameEvent {
        if (!$variableHolder instanceof Daedalus) {
            throw new \Exception('a daedalus should be provided to create a daedalusVariableEvent');
        }

        if ($variableHolder->hasVariable($this->targetVariable)) {
            return new DaedalusVariableEvent($variableHolder, $this->targetVariable, $this->quantity, $tags, $date);
        }
        if ($variableHolder->hasStatus($this->targetVariable)) {
            return $this->createStatusVariableEvent($tags, $date, $variableHolder);
        }

        return null;
    }

    private function createPlayerVariableEvent(
        array $tags,
        \DateTime $date,
        ModifierHolderInterface $variableHolder
    ): ?AbstractGameEvent {
        if (!$variableHolder instanceof Player) {
            throw new \Exception('a player should be provided to create a playerVariableEvent');
        }

        if ($variableHolder->hasVariable($this->targetVariable)) {
            return new PlayerVariableEvent($variableHolder, $this->targetVariable, $this->quantity, $tags, $date);
        }
        if ($variableHolder->hasStatus($this->targetVariable)) {
            return $this->createStatusVariableEvent($tags, $date, $variableHolder);
        }

        return null;
    }

    private function createEquipmentVariableEvent(
        array $tags,
        \DateTime $date,
        ModifierHolderInterface $variableHolder
    ): ?AbstractGameEvent {
        if (!$variableHolder instanceof GameEquipment) {
            throw new \Exception('a player should be provided to create a playerVariableEvent');
        }

        if ($variableHolder->hasStatus($this->targetVariable)) {
            return $this->createStatusVariableEvent($tags, $date, $variableHolder);
        }

        return null;
    }

    private function createStatusVariableEvent(
        array $tags,
        \DateTime $date,
        StatusHolderInterface $variableHolder
    ): ?AbstractGameEvent {
        $status = $variableHolder->getStatusByName($this->targetVariable);
        if ($status === null || !($status instanceof ChargeStatus)) {
            return null;
        }

        return new ChargeStatusEvent($status, $variableHolder, $this->quantity, $tags, $date);
    }

    private function createProjectVariableEvent(
        array $tags,
        \DateTime $date,
        ModifierHolderInterface $variableHolder
    ): ?AbstractGameEvent {
        if (!$variableHolder instanceof Project) {
            throw new \Exception('a project should be provided to create a projectVariableEvent');
        }

        if ($variableHolder->hasVariable($this->targetVariable)) {
            return new ProjectEfficiencyVariableEvent($variableHolder, $this->quantity, $tags, $date);
        }

        return null;
    }
}
