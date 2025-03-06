<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Skill\Enum\SkillEnum;

#[ORM\Entity]
class TradeOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Trade::class, inversedBy: 'tradeOptions')]
    private Trade $trade;

    #[ORM\Column(type: 'string', enumType: SkillEnum::class, nullable: false, options: ['default' => SkillEnum::NULL])]
    private SkillEnum $requiredSkill = SkillEnum::NULL;

    #[ORM\OneToMany(targetEntity: TradeAsset::class, mappedBy: 'requiredTradeOption', cascade: ['all'], orphanRemoval: true)]
    private Collection $requiredAssets;

    #[ORM\OneToMany(targetEntity: TradeAsset::class, mappedBy: 'offeredTradeOption', cascade: ['all'], orphanRemoval: true)]
    private Collection $offeredAssets;

    public function __construct(ArrayCollection $requiredAssets, ArrayCollection $offeredAssets, SkillEnum $requiredSkill = SkillEnum::NULL)
    {
        $this->requiredAssets = new ArrayCollection();
        $this->offeredAssets = new ArrayCollection();
        $this->requiredSkill = $requiredSkill;

        foreach ($requiredAssets as $requiredAsset) {
            $this->addRequiredAsset($requiredAsset);
        }
        foreach ($offeredAssets as $offeredAsset) {
            $this->addOfferedAsset($offeredAsset);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRequiredSkill(): SkillEnum
    {
        return $this->requiredSkill;
    }

    /**
     * @return ArrayCollection<array-key, TradeAsset>
     */
    public function getRequiredAssets(): ArrayCollection
    {
        return new ArrayCollection($this->requiredAssets->toArray());
    }

    /**
     * @return ArrayCollection<array-key, TradeAsset>
     */
    public function getOfferedAssets(): ArrayCollection
    {
        return new ArrayCollection($this->offeredAssets->toArray());
    }

    public function setTrade(Trade $trade): void
    {
        $this->trade = $trade;
    }

    private function addRequiredAsset(TradeAsset $tradeAsset): void
    {
        if (!$this->requiredAssets->contains($tradeAsset)) {
            $this->requiredAssets->add($tradeAsset);
            $tradeAsset->setRequiredTradeOption($this);
        }
    }

    private function addOfferedAsset(TradeAsset $tradeAsset): void
    {
        if (!$this->offeredAssets->contains($tradeAsset)) {
            $this->offeredAssets->add($tradeAsset);
            $tradeAsset->setOfferedTradeOption($this);
        }
    }
}
