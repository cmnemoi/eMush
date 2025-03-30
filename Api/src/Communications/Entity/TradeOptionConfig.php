<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\ConfigData\TradeAssetConfigData;
use Mush\Communications\Dto\TradeOptionConfigDto;
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

    #[ORM\ManyToMany(targetEntity: TradeAssetConfig::class)]
    #[ORM\JoinTable(name: 'trade_option_required_assets')]
    #[ORM\JoinColumn(name: 'trade_option_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'trade_asset_id', referencedColumnName: 'id')]
    private Collection $requiredAssetConfigs;

    #[ORM\ManyToMany(targetEntity: TradeAssetConfig::class)]
    #[ORM\JoinTable(name: 'trade_option_offered_assets')]
    #[ORM\JoinColumn(name: 'trade_option_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'trade_asset_id', referencedColumnName: 'id')]
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

        $this->setRequiredAssetConfigs($requiredAssetConfigs);
        $this->setOfferedAssetConfigs($offeredAssetConfigs);
    }

    public static function fromDto(TradeOptionConfigDto $tradeOptionConfigDto): self
    {
        return new self(
            name: $tradeOptionConfigDto->name,
            requiredSkill: $tradeOptionConfigDto->requiredSkill,
            requiredAssetConfigs: array_map(
                static fn (string $tradeAssetConfigName) => TradeAssetConfig::fromDto(TradeAssetConfigData::getByName($tradeAssetConfigName)),
                $tradeOptionConfigDto->requiredAssets,
            ),
            offeredAssetConfigs: array_map(
                static fn (string $tradeAssetConfigName) => TradeAssetConfig::fromDto(TradeAssetConfigData::getByName($tradeAssetConfigName)),
                $tradeOptionConfigDto->offeredAssets,
            ),
        );
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

    /**
     * @return Collection<int, TradeAssetConfig>
     */
    public function getOfferedAssetConfigs(): Collection
    {
        return $this->offeredAssetConfigs;
    }

    public function setTradeConfig(TradeConfig $tradeConfig): void
    {
        $this->tradeConfig = $tradeConfig;
    }

    public function update(self $tradeOptionConfig): void
    {
        $this->name = $tradeOptionConfig->name;
        $this->requiredSkill = $tradeOptionConfig->requiredSkill;
        $this->requiredAssetConfigs = $tradeOptionConfig->requiredAssetConfigs;
        $this->offeredAssetConfigs = $tradeOptionConfig->offeredAssetConfigs;
    }

    private function setRequiredAssetConfigs(array $requiredAssetConfigs): void
    {
        foreach ($requiredAssetConfigs as $requiredAssetConfig) {
            if ($this->requiredAssetConfigs->contains($requiredAssetConfig)) {
                continue;
            }
            $this->requiredAssetConfigs->add($requiredAssetConfig);
        }
    }

    private function setOfferedAssetConfigs(array $offeredAssetConfigs): void
    {
        foreach ($offeredAssetConfigs as $offeredAssetConfig) {
            if ($this->offeredAssetConfigs->contains($offeredAssetConfig)) {
                continue;
            }
            $this->offeredAssetConfigs->add($offeredAssetConfig);
        }
    }
}
