<?php

namespace Mush\Project\Event;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectVariablesEnum;

final class ProjectProgressEvent extends AbstractGameEvent implements VariableEventInterface
{
    public function __construct(
        private Project $project,
        private int $quantity,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);

        $this->setQuantity($quantity);
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function getRoundedQuantity(): int
    {
        return $this->quantity;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        if ($quantity < 0) {
            $key = array_search(VariableEventInterface::GAIN, $this->tags, true);

            if ($key === false) {
                $this->addTag(VariableEventInterface::LOSS);
            } elseif (!\in_array(VariableEventInterface::LOSS, $this->tags, true)) {
                $this->tags[$key] = VariableEventInterface::LOSS;
            }
        } elseif ($quantity > 0) {
            $key = array_search(VariableEventInterface::LOSS, $this->tags, true);

            if ($key === false) {
                $this->addTag(VariableEventInterface::GAIN);
            } elseif (!\in_array(VariableEventInterface::GAIN, $this->tags, true)) {
                $this->tags[$key] = VariableEventInterface::GAIN;
            }
        }

        return $this;
    }

    /**
     * @psalm-suppress UndefinedMethod
     */
    public function getVariable(): GameVariable
    {
        return $this->project->getVariableByName(ProjectVariablesEnum::PROGRESS->value);
    }

    public function getVariableName(): string
    {
        return ProjectVariablesEnum::PROGRESS->value;
    }

    public function getLogParameters(): array
    {
        return [];
    }
}
