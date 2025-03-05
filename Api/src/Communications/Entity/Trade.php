<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\Enum\TradeEnum;
use Mush\Hunter\Entity\Hunter;

#[ORM\Entity]
class Trade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', enumType: TradeEnum::class, nullable: false, options: ['default' => TradeEnum::NULL])]
    private TradeEnum $name = TradeEnum::NULL;

    #[ORM\OneToMany(targetEntity: TradeOption::class, mappedBy: 'trade', cascade: ['all'], orphanRemoval: true)]
    private Collection $tradeOptions;

    private int $hunterId;

    #[ORM\OneToOne(targetEntity: Hunter::class)]
    private Hunter $hunter;

    public function __construct(
        TradeEnum $name,
        Collection $tradeOptions,
        int $hunterId,
    ) {
        $this->name = $name;
        $this->tradeOptions = $tradeOptions;
        $this->hunterId = $hunterId;
    }

    public function getName(): TradeEnum
    {
        return $this->name;
    }

    public function getHunterId(): int
    {
        return $this->hunterId;
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setHunterId(int $hunterId): void
    {
        $this->hunterId = $hunterId;
    }

    /**
     * @deprecated Should be used only in Doctrine repositories. Use getHunterId() instead.
     */
    public function getHunter(): Hunter
    {
        return $this->hunter;
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setHunter(Hunter $hunter): void
    {
        $this->hunter = $hunter;
    }
}
