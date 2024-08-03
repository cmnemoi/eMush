<?php

namespace Mush\Status\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Entity\SkillConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

#[ORM\Entity]
class ChargeStatusConfig extends StatusConfig
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $chargeVisibility = VisibilityEnum::PUBLIC;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $chargeStrategy = ChargeStrategyTypeEnum::NONE;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $maxCharge = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $startCharge = 0;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $dischargeStrategies = [ChargeStrategyTypeEnum::NONE];

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $autoRemove = false;

    public static function createNull(): self
    {
        return (new self())->setId(0);
    }

    public static function fromConfigData(array $configData): self
    {
        $statusConfig = (new self())
            ->setChargeVisibility($configData['chargeVisibility'])
            ->setChargeStrategy($configData['chargeStrategy'])
            ->setMaxCharge($configData['maxCharge'])
            ->setStartCharge($configData['startCharge'])
            ->setDischargeStrategies($configData['dischargeStrategies'])
            ->setAutoRemove($configData['autoRemove'])
            ->setName($configData['name'])
            ->setStatusName($configData['statusName']);

        foreach ($configData['actionConfigs'] as $actionConfig) {
            $statusConfig->addActionConfig(ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::from($actionConfig))));
        }
        foreach ($configData['skillConfigs'] as $skillConfig) {
            $statusConfig->addSkillConfig(SkillConfig::createFromDto(SkillConfigData::getByName($skillConfig)));
        }

        return $statusConfig;
    }

    public function getChargeVisibility(): string
    {
        return $this->chargeVisibility;
    }

    /**
     * @return static
     */
    public function setChargeVisibility(string $chargeVisibility): self
    {
        $this->chargeVisibility = $chargeVisibility;

        return $this;
    }

    public function getChargeStrategy(): string
    {
        return $this->chargeStrategy;
    }

    /**
     * @return static
     */
    public function setChargeStrategy(string $chargeStrategy): self
    {
        $this->chargeStrategy = $chargeStrategy;

        return $this;
    }

    public function isAutoRemove(): bool
    {
        return $this->autoRemove;
    }

    /**
     * @return static
     */
    public function setAutoRemove(bool $autoRemove): self
    {
        $this->autoRemove = $autoRemove;

        return $this;
    }

    public function getMaxCharge(): ?int
    {
        return $this->maxCharge;
    }

    /**
     * @return static
     */
    public function setMaxCharge(?int $maxCharge): self
    {
        $this->maxCharge = $maxCharge;

        return $this;
    }

    public function getStartCharge(): int
    {
        return $this->startCharge;
    }

    /**
     * @return static
     */
    public function setStartCharge(int $startCharge): self
    {
        $this->startCharge = $startCharge;

        return $this;
    }

    /**
     * @return static
     */
    public function setDischargeStrategies(array $dischargeStrategies): self
    {
        $this->dischargeStrategies = $dischargeStrategies;

        return $this;
    }

    public function getDischargeStrategies(): array
    {
        return $this->dischargeStrategies;
    }

    private function toSnapshot(): array
    {
        return [
            'name' => $this->name,
            'statusName' => $this->statusName,
            'visibility' => $this->visibility,
            'autoRemove' => $this->autoRemove,
            'startCharge' => $this->startCharge,
            'maxCharge' => $this->maxCharge,
            'chargeVisibility' => $this->chargeVisibility,
            'chargeStrategy' => $this->chargeStrategy,
            'dischargeStrategies' => $this->dischargeStrategies,
        ];
    }

    private function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
