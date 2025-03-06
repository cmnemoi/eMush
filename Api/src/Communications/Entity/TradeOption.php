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

    #[ORM\Column(type: 'string', enumType: SkillEnum::class, nullable: false, options: ['default' => SkillEnum::NULL])]
    private SkillEnum $requiredSkill = SkillEnum::NULL;

    #[ORM\OneToMany(targetEntity: TradeAsset::class, mappedBy: 'tradeOption', cascade: ['all'], orphanRemoval: true)]
    private Collection $requiredAssets;

    #[ORM\OneToMany(targetEntity: TradeAsset::class, mappedBy: 'tradeOption', cascade: ['all'], orphanRemoval: true)]
    private Collection $offeredAssets;

    public function __construct(ArrayCollection $requiredAssets, ArrayCollection $offeredAssets, SkillEnum $requiredSkill = SkillEnum::NULL)
    {
        $this->requiredAssets = $requiredAssets;
        $this->offeredAssets = $offeredAssets;
        $this->requiredSkill = $requiredSkill;
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
}
