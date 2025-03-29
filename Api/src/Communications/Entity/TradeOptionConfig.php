<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Skill\Enum\SkillEnum;

#[ORM\Entity]
#[ORM\Table(name: 'trade_option_config')]
class TradeOptionConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $name = '';

    #[ORM\Column(type: 'string', enumType: SkillEnum::class, nullable: false, options: ['default' => SkillEnum::NULL])]
    private SkillEnum $requiredSkill = SkillEnum::NULL;

    #[ORM\ManyToOne(targetEntity: TradeConfig::class, inversedBy: 'tradeOptionConfigs')]
    private TradeConfig $tradeConfig;

    #[ORM\OneToMany(mappedBy: 'tradeOptionConfigRequired', targetEntity: TradeAssetConfig::class, cascade: ['persist', 'remove'])]
    private Collection $requiredAssetConfigs;

    #[ORM\OneToMany(mappedBy: 'tradeOptionConfigOffered', targetEntity: TradeAssetConfig::class, cascade: ['persist', 'remove'])]
    private Collection $offeredAssetConfigs;

    public function __construct(
        string $name = '',
        SkillEnum $requiredSkill = SkillEnum::NULL,
        array $requiredAssetConfigs = [],
        array $offeredAssetConfigs = [],
    ) {
        $this->name = $name;
        $this->requiredSkill = $requiredSkill;
        $this->requiredAssetConfigs = new ArrayCollection();
        $this->offeredAssetConfigs = new ArrayCollection();

        foreach ($requiredAssetConfigs as $requiredAssetConfig) {
            $this->addRequiredAssetConfig($requiredAssetConfig);
        }

        foreach ($offeredAssetConfigs as $offeredAssetConfig) {
            $this->addOfferedAssetConfig($offeredAssetConfig);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRequiredSkill(): SkillEnum
    {
        return $this->requiredSkill;
    }

    /**
     * @return Collection<int, TradeAssetConfig>
     */
    public function getRequiredAssetConfigs(): Collection
    {
        return $this->requiredAssetConfigs;
    }

    public function addRequiredAssetConfig(TradeAssetConfig $requiredAssetConfig): self
    {
        if (!$this->requiredAssetConfigs->contains($requiredAssetConfig)) {
            $this->requiredAssetConfigs->add($requiredAssetConfig);
            $requiredAssetConfig->setRequiredTradeOptionConfig($this);
        }

        return $this;
    }

    public function removeRequiredAssetConfig(TradeAssetConfig $requiredAssetConfig): self
    {
        if ($this->requiredAssetConfigs->removeElement($requiredAssetConfig)) {
            $requiredAssetConfig->setRequiredTradeOptionConfig(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, TradeAssetConfig>
     */
    public function getOfferedAssetConfigs(): Collection
    {
        return $this->offeredAssetConfigs;
    }

    public function addOfferedAssetConfig(TradeAssetConfig $offeredAssetConfig): self
    {
        if (!$this->offeredAssetConfigs->contains($offeredAssetConfig)) {
            $this->offeredAssetConfigs->add($offeredAssetConfig);
            $offeredAssetConfig->setOfferedTradeOptionConfig($this);
        }

        return $this;
    }

    public function removeOfferedAssetConfig(TradeAssetConfig $offeredAssetConfig): self
    {
        if ($this->offeredAssetConfigs->removeElement($offeredAssetConfig)) {
            $offeredAssetConfig->setOfferedTradeOptionConfig(null);
        }

        return $this;
    }

    public function setTradeConfig(TradeConfig $tradeConfig): self
    {
        $this->tradeConfig = $tradeConfig;

        return $this;
    }

    public function update(self $tradeOptionConfig): self
    {
        $this->name = $tradeOptionConfig->name;
        $this->requiredSkill = $tradeOptionConfig->requiredSkill;

        // Clear and repopulate the collections
        // First, create copies of the current collections to avoid modification during iteration
        $currentRequiredAssets = new ArrayCollection($this->requiredAssetConfigs->toArray());
        $currentOfferedAssets = new ArrayCollection($this->offeredAssetConfigs->toArray());

        // Remove all existing assets
        foreach ($currentRequiredAssets as $asset) {
            $this->removeRequiredAssetConfig($asset);
        }
        foreach ($currentOfferedAssets as $asset) {
            $this->removeOfferedAssetConfig($asset);
        }

        // Add the new assets
        foreach ($tradeOptionConfig->getRequiredAssetConfigs() as $asset) {
            $this->addRequiredAssetConfig($asset);
        }
        foreach ($tradeOptionConfig->getOfferedAssetConfigs() as $asset) {
            $this->addOfferedAssetConfig($asset);
        }

        return $this;
    }
}
