<?php

namespace Mush\Action\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\PlayerVariableEnum;

#[ORM\Entity]
class Action implements GameVariableHolderInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $actionName;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $types = [];

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $target = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $scope;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $visibilities = [
        ActionOutputEnum::SUCCESS => VisibilityEnum::PUBLIC,
        ActionOutputEnum::FAIL => VisibilityEnum::PRIVATE,
    ];

    #[ORM\ManyToOne(targetEntity: ActionVariables::class, cascade: ['ALL'])]
    private ActionVariables $actionVariables;

    public function __construct()
    {
        $this->actionVariables = new ActionVariables();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function buildName(string $configName): self
    {
        $this->name = $this->actionName . '_' . $configName;

        return $this;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function setActionName(string $actionName): self
    {
        $this->actionName = $actionName;

        return $this;
    }

    public function getTypes(): array
    {
        $types = $this->types;

        if (in_array($this->visibilities[ActionOutputEnum::SUCCESS], [VisibilityEnum::SECRET, VisibilityEnum::COVERT])) {
            $types[] = $this->visibilities[ActionOutputEnum::SUCCESS];
        }

        return $types;
    }

    public function getActionTags(): array
    {
        $tags = $this->getTypes();
        $tags[] = $this->actionName;

        return $tags;
    }

    public function setTypes(array $types): self
    {
        $this->types = $types;

        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getVariableByName(string $variableName): GameVariable
    {
        return $this->actionVariables->getVariableByName($variableName);
    }

    public function getGameVariables(): ActionVariables
    {
        return $this->actionVariables;
    }

    public function hasVariable(string $variableName): bool
    {
        return $this->actionVariables->hasVariable($variableName);
    }

    public function getActionVariablesArray(): array
    {
        return $this->actionVariables->getVariablesAsArray();
    }

    public function setActionVariablesArray(array $actionVariables): self
    {
        $this->actionVariables->updateVariablesFromArray($actionVariables);

        return $this;
    }

    public function getActionCost(): int
    {
        return $this->actionVariables->getValueByName(PlayerVariableEnum::ACTION_POINT);
    }

    public function setActionCost(int $actionCost): self
    {
        $this->actionVariables->setValueByName($actionCost, PlayerVariableEnum::ACTION_POINT);

        return $this;
    }

    public function getMovementCost(): int
    {
        return $this->actionVariables->getValueByName(PlayerVariableEnum::MOVEMENT_POINT);
    }

    public function setMovementCost(int $movementCost): self
    {
        $this->actionVariables->setValueByName($movementCost, PlayerVariableEnum::MOVEMENT_POINT);

        return $this;
    }

    public function getMoralCost(): int
    {
        return $this->actionVariables->getValueByName(PlayerVariableEnum::MORAL_POINT);
    }

    public function setMoralCost(int $moralCost): self
    {
        $this->actionVariables->setValueByName($moralCost, PlayerVariableEnum::MORAL_POINT);

        return $this;
    }

    public function getSuccessRate(): int
    {
        return $this->actionVariables->getValueByName(ActionVariableEnum::PERCENTAGE_SUCCESS);
    }

    public function setSuccessRate(int $successRate): self
    {
        $gameVariable = $this->actionVariables->getVariableByName(ActionVariableEnum::PERCENTAGE_SUCCESS);

        // Set max success value
        if ($successRate >= 100) {
            $gameVariable->setMaxValue(100);
        } else {
            $gameVariable->setMaxValue(99);
        }

        // Set min success value
        if ($successRate <= 0) {
            $gameVariable->setMinValue(0);
        } else {
            $gameVariable->setMinValue(1);
        }
        $gameVariable->setValue($successRate);

        return $this;
    }

    public function getInjuryRate(): int
    {
        return $this->actionVariables->getValueByName(ActionVariableEnum::PERCENTAGE_INJURY);
    }

    public function setInjuryRate(int $injuryRate): self
    {
        $this->actionVariables->setValueByName($injuryRate, ActionVariableEnum::PERCENTAGE_INJURY);

        return $this;
    }

    public function getDirtyRate(): int
    {
        return $this->actionVariables->getValueByName(ActionVariableEnum::PERCENTAGE_DIRTINESS);
    }

    public function setDirtyRate(int $dirtyRate): self
    {
        $gameVariable = $this->actionVariables->getVariableByName(ActionVariableEnum::PERCENTAGE_DIRTINESS);
        $gameVariable->setValue($dirtyRate);
        $gameVariable->setMaxValue(100);
        $gameVariable->setMinValue(0);

        return $this;
    }

    public function makeSuperDirty(): self
    {
        $gameVariable = $this->actionVariables->getVariableByName(ActionVariableEnum::PERCENTAGE_DIRTINESS);
        $this->types[] = ActionTypeEnum::ACTION_SUPER_DIRTY;
        $gameVariable->setMinValue(100);

        return $this;
    }

    public function getVisibility(string $actionOutput): string
    {
        if (key_exists($actionOutput, $this->visibilities)) {
            return $this->visibilities[$actionOutput];
        }

        return VisibilityEnum::HIDDEN;
    }

    public function setVisibility(string $actionOutput, string $visibility): self
    {
        $this->visibilities[$actionOutput] = $visibility;

        return $this;
    }

    public function setCriticalRate(int $criticalRate): self
    {
        $this->actionVariables->setValueByName($criticalRate, ActionVariableEnum::PERCENTAGE_CRITICAL);

        return $this;
    }

    public function getCriticalRate(): int
    {
        return $this->actionVariables->getValueByName(ActionVariableEnum::PERCENTAGE_CRITICAL);
    }

    public function getOutputQuantity(): int
    {
        return $this->actionVariables->getValueByName(ActionVariableEnum::OUTPUT_QUANTITY);
    }

    public function setOutputQuantity(int $outputQuantity): self
    {
        $this->actionVariables->setValueByName($outputQuantity, ActionVariableEnum::OUTPUT_QUANTITY);

        return $this;
    }
}
