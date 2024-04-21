<?php

declare(strict_types=1);

namespace Mush\Project\Event;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectEfficiencyVariable;

final class ProjectEfficiencyVariableEvent extends AbstractGameEvent implements VariableEventInterface
{
    private float $quantity;

    public function __construct(
        private Project $project,
        float $delta,
        protected array $tags = [],
        protected \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);

        $this->setQuantity($delta);
    }

    public function getProject(): Project
    {
        return $this->project;
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
        return $this->project->getVariableByName($this->getVariableName());
    }

    public function getVariableName(): string
    {
        return ProjectEfficiencyVariable::NAME;
    }

    public function getLogParameters(): array
    {
        $parameters = [];
        $parameters['quantity'] = $this->getQuantity();

        return $parameters;
    }
}
