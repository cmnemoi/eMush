<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Communications\Enum\XylophEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Status\Entity\ChargeStatus;

#[ORM\Entity]
class XylophEntry implements ModifierProviderInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: XylophConfig::class)]
    private XylophConfig $xylophConfig;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isDecoded;

    private int $daedalusId;

    #[ORM\ManyToOne(targetEntity: Daedalus::class)]
    private Daedalus $daedalus;

    public function __construct(XylophConfig $xylophConfig, int $daedalusId, bool $isDecoded = false)
    {
        $this->xylophConfig = $xylophConfig;
        $this->daedalusId = $daedalusId;
        $this->isDecoded = $isDecoded;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): XylophEnum
    {
        return $this->xylophConfig->getName();
    }

    public function isDecoded(): bool
    {
        return $this->isDecoded === true;
    }

    public function isUndecoded(): bool
    {
        return $this->isDecoded === false;
    }

    public function unlockDatabase(): void
    {
        $this->isDecoded = true;
    }

    public function getWeight(): int
    {
        return $this->xylophConfig->getWeight();
    }

    public function setWeight(int $quantity): void
    {
        $this->xylophConfig->setWeight($quantity);
    }

    public function getQuantity(): int
    {
        return $this->xylophConfig->getQuantity();
    }

    public function setQuantity(int $value): void
    {
        $this->xylophConfig->setQuantity($value);
    }

    /**
     * @return ArrayCollection<int, AbstractModifierConfig>
     */
    public function getModifierConfigs(): ArrayCollection
    {
        return new ArrayCollection($this->xylophConfig->getModifierConfigs()->toArray());
    }

    public function getUsedCharge(string $actionName): ?ChargeStatus
    {
        return null;
    }

    public function getOperationalStatus(string $actionName): ActionProviderOperationalStateEnum
    {
        if ($this->isDecoded()) {
            return ActionProviderOperationalStateEnum::OPERATIONAL;
        }

        return ActionProviderOperationalStateEnum::DEACTIVATED;
    }

    /**
     * @return ArrayCollection<int, AbstractModifierConfig>
     */
    public function getAllModifierConfigs(): ArrayCollection
    {
        return $this->getModifierConfigs();
    }

    public function getDaedalusId(): int
    {
        return $this->daedalusId;
    }

    public function getUpdatedAtOrThrow(): \DateTime
    {
        return $this->updatedAt ?? throw new \RuntimeException("Xyloph entry {$this->id} should have an updated at date");
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setDaedalusId(int $daedalusId): void
    {
        $this->daedalusId = $daedalusId;
    }

    /**
     * @deprecated Should be used only in Doctrine repositories. Use getDaedalusId() instead.
     */
    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setDaedalus(Daedalus $daedalus): void
    {
        $this->daedalus = $daedalus;
    }
}
