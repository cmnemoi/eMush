<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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

    private int $transportId;

    #[ORM\OneToOne(targetEntity: Hunter::class)]
    #[ORM\JoinColumn(name: 'transport_id')]
    private Hunter $transport;

    public function __construct(TradeEnum $name, ArrayCollection $tradeOptions, int $transportId)
    {
        $this->name = $name;
        $this->tradeOptions = $tradeOptions;
        $this->transportId = $transportId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): TradeEnum
    {
        return $this->name;
    }

    public function getTradeOptions(): ArrayCollection
    {
        return new ArrayCollection($this->tradeOptions->toArray());
    }

    public function getTransportId(): int
    {
        return $this->transportId;
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setTransportId(int $transportId): void
    {
        $this->transportId = $transportId;
    }

    /**
     * @deprecated Should be used only in Doctrine repositories. Use getHunterId() instead.
     */
    public function getTransport(): Hunter
    {
        return $this->transport;
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setTransport(Hunter $transport): void
    {
        $this->transport = $transport;
    }
}
