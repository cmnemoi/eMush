<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

#[ORM\Entity]
class ChargeStatus extends Status implements GameVariableHolderInterface
{
    #[ORM\OneToOne(targetEntity: GameVariableCollection::class, cascade: ['ALL'])]
    private ChargeVariable $charge;

    public function __construct(StatusHolderInterface $statusHolder, ChargeStatusConfig $statusConfig)
    {
        parent::__construct($statusHolder, $statusConfig);

        $this->charge = new ChargeVariable($statusConfig);
    }

    public function getGameVariables(): GameVariableCollection
    {
        return $this->charge;
    }

    public function hasVariable(string $variableName): bool
    {
        return $this->charge->hasVariable($variableName);
    }

    public function getVariableByName(string $variableName): GameVariable
    {
        return $this->charge->getVariableByName($variableName);
    }

    public function getCharge(): int
    {
        return $this->getVariableByName($this->getName())->getValue();
    }

    public function setCharge(int $charge): static
    {
        $this->getVariableByName($this->getName())->setValue($charge);

        return $this;
    }

    public function isCharged(): bool
    {
        return !$this->getVariableByName($this->getName())->isMin();
    }

    public function getStatusConfig(): ChargeStatusConfig
    {
        if (!$this->statusConfig instanceof ChargeStatusConfig) {
            throw new UnexpectedTypeException($this->statusConfig, ChargeStatusConfig::class);
        }

        return $this->statusConfig;
    }

    public function getChargeVisibility(): string
    {
        return $this->getStatusConfig()->getChargeVisibility();
    }

    public function getStrategy(): ?string
    {
        return $this->getStatusConfig()->getChargeStrategy();
    }

    public function getDischargeStrategies(): ?array
    {
        return $this->getStatusConfig()->getDischargeStrategies();
    }

    public function hasDischargeStrategy(string $strategy): bool
    {
        $dischargeStrategies = $this->getDischargeStrategies();
        if ($dischargeStrategies === null) {
            return false;
        }

        return \in_array($strategy, $dischargeStrategies, strict: true);
    }

    public function getThreshold(): ?int
    {
        return $this->getStatusConfig()->getMaxCharge();
    }

    public function isAutoRemove(): bool
    {
        return $this->getStatusConfig()->isAutoRemove();
    }

    public function getUsedCharge(ActionEnum $actionName): ?self
    {
        if ($this->hasDischargeStrategy($actionName->value)) {
            return $this;
        }

        return null;
    }

    public function getOperationalStatus(ActionEnum $actionName): ActionProviderOperationalStateEnum
    {
        if ($this->hasDischargeStrategy($actionName->value) && !$this->isCharged()) {
            return ActionProviderOperationalStateEnum::DISCHARGED;
        }

        return ActionProviderOperationalStateEnum::OPERATIONAL;
    }

    public function getMaturationTimeOrThrow(): int
    {
        if ($this->getName() !== EquipmentStatusEnum::PLANT_YOUNG) {
            throw new \LogicException("Charge status should be plant_young status for its maturation time be calculated, got {$this->getName()} instead.");
        }

        $plant = $this->getItemOwnerOrThrow();
        $parasiteElimProject = $plant->getDaedalus()->getProjectByName(ProjectName::PARASITE_ELIM);

        $maturationTime = $this->getMaxChargeOrThrow();
        if ($parasiteElimProject->isFinished() && $plant->isInPlaceByName(RoomEnum::HYDROPONIC_GARDEN)) {
            $maturationTime -= $parasiteElimProject->getActivationRate();
        }

        return $maturationTime;
    }

    private function getItemOwnerOrThrow(): GameItem
    {
        $owner = $this->getOwner();
        if (!$owner instanceof GameItem) {
            throw new \LogicException("{$owner->getName()} entity should be a GameItem, got {$owner->getClassName()}" instead.);
        }

        return $owner;
    }

    private function getMaxChargeOrThrow(): int
    {
        $maxCharge = $this->getVariableByName($this->getName())->getMaxValue();
        if ($maxCharge === null) {
            throw new \LogicException('Max charge is not set for {$this->getName()} status.');
        }

        return $maxCharge;
    }
}
