<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Status\Entity\Config\ChargeStatusConfig;
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

        return in_array($strategy, $dischargeStrategies, strict: true);
    }

    public function getThreshold(): ?int
    {
        return $this->getStatusConfig()->getMaxCharge();
    }

    public function isAutoRemove(): bool
    {
        return $this->getStatusConfig()->isAutoRemove();
    }
}
