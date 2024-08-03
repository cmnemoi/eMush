<?php

namespace Mush\Action\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\PlayerVariableEnum;

#[ORM\Entity]
#[ORM\Table(name: 'action')]
class ActionConfig implements GameVariableHolderInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false, enumType: ActionEnum::class)]
    private ActionEnum $actionName;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $types = [];

    #[ORM\Column(type: 'string', enumType: ActionHolderEnum::class, nullable: false, options: ['default' => ActionHolderEnum::NULL])]
    private ActionHolderEnum $displayHolder;

    #[ORM\Column(type: 'string', enumType: ActionRangeEnum::class, nullable: false, options: ['default' => ActionRangeEnum::NULL])]
    private ActionRangeEnum $range;

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

    public static function fromConfigData(array $configData): self
    {
        $actionConfig = new self();
        $actionConfig
            ->setName($configData['name'])
            ->setActionName($configData['action_name'])
            ->setTypes($configData['types'])
            ->setDisplayHolder($configData['target'])
            ->setRange($configData['scope']);

        $gameVariables = $actionConfig->getGameVariables();
        $gameVariables->setValuesByName($configData['percentageInjury'], ActionVariableEnum::PERCENTAGE_INJURY);
        $gameVariables->setValuesByName($configData['percentageSuccess'], ActionVariableEnum::PERCENTAGE_SUCCESS);
        $gameVariables->setValuesByName($configData['percentageCritical'], ActionVariableEnum::PERCENTAGE_CRITICAL);
        $gameVariables->setValuesByName($configData['outputQuantity'], ActionVariableEnum::OUTPUT_QUANTITY);

        $gameVariables->setValuesByName($configData['actionPoint'], PlayerVariableEnum::ACTION_POINT);
        $gameVariables->setValuesByName($configData['moralPoint'], PlayerVariableEnum::MORAL_POINT);
        $gameVariables->setValuesByName($configData['movementPoint'], PlayerVariableEnum::MOVEMENT_POINT);

        $gameVariables->setValuesByName($configData['percentageDirtiness'], ActionVariableEnum::PERCENTAGE_DIRTINESS);
        if ($configData['percentageDirtiness']['min_value'] >= 100) {
            $actionConfig->makeSuperDirty();
        }

        $actionConfig->setVisibilities($configData['visibilities']);

        return $actionConfig;
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
        $this->name = $this->actionName->value . '_' . $configName;

        return $this;
    }

    public function getActionName(): ActionEnum
    {
        return $this->actionName;
    }

    public function setActionName(ActionEnum $actionName): self
    {
        $this->actionName = $actionName;

        return $this;
    }

    public function getTypes(): array
    {
        $types = [];
        foreach ($this->types as $type) {
            $types[] = $type->value;
        }

        if (\in_array($this->visibilities[ActionOutputEnum::SUCCESS], [VisibilityEnum::SECRET, VisibilityEnum::COVERT], true)) {
            $types[] = $this->visibilities[ActionOutputEnum::SUCCESS];
        }

        return $types;
    }

    public function getActionTags(): array
    {
        $tags = $this->getTypes();
        $tags[] = $this->actionName->value;

        return $tags;
    }

    public function setTypes(array $types): self
    {
        $this->types = $types;

        return $this;
    }

    public function getDisplayHolder(): ActionHolderEnum
    {
        return $this->displayHolder;
    }

    public function setDisplayHolder(ActionHolderEnum $displayHolder): self
    {
        $this->displayHolder = $displayHolder;

        return $this;
    }

    public function getRange(): ActionRangeEnum
    {
        return $this->range;
    }

    public function setRange(ActionRangeEnum $range): self
    {
        $this->range = $range;

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
        if ($actionCost === 0) {
            $this->types[] = ActionTypeEnum::ACTION_ZERO_ACTION_COST;
        }

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
        if (\array_key_exists($actionOutput, $this->visibilities)) {
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

    public function shouldTriggerRoomTrap(): bool
    {
        return ActionEnum::getActionsWhichTriggerRoomTraps()->contains($this->actionName);
    }

    public function updateFromConfigData(array $configData): self
    {
        $this->setName($configData['name'])
            ->setActionName($configData['action_name'])
            ->setTypes($configData['types'])
            ->setDisplayHolder($configData['target'])
            ->setRange($configData['scope']);

        $gameVariables = $this->getGameVariables();
        $gameVariables->setValuesByName($configData['percentageInjury'], ActionVariableEnum::PERCENTAGE_INJURY);
        $gameVariables->setValuesByName($configData['percentageSuccess'], ActionVariableEnum::PERCENTAGE_SUCCESS);
        $gameVariables->setValuesByName($configData['percentageCritical'], ActionVariableEnum::PERCENTAGE_CRITICAL);
        $gameVariables->setValuesByName($configData['outputQuantity'], ActionVariableEnum::OUTPUT_QUANTITY);

        $gameVariables->setValuesByName($configData['actionPoint'], PlayerVariableEnum::ACTION_POINT);
        $gameVariables->setValuesByName($configData['moralPoint'], PlayerVariableEnum::MORAL_POINT);
        $gameVariables->setValuesByName($configData['movementPoint'], PlayerVariableEnum::MOVEMENT_POINT);

        $gameVariables->setValuesByName($configData['percentageDirtiness'], ActionVariableEnum::PERCENTAGE_DIRTINESS);
        if ($configData['percentageDirtiness']['min_value'] >= 100) {
            $this->makeSuperDirty();
        }

        $this->setVisibilities($configData['visibilities']);

        return $this;
    }

    private function setVisibilities(array $visibilities): self
    {
        foreach ($visibilities as $visibilityType => $visibility) {
            $this->setVisibility($visibilityType, $visibility);
        }

        return $this;
    }
}
