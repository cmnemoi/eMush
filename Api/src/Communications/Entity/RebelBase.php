<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Status\Entity\ChargeStatus;

#[ORM\Entity]
class RebelBase implements ModifierProviderInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\OneToOne(targetEntity: RebelBaseConfig::class)]
    private RebelBaseConfig $rebelBaseConfig;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isContacting;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $signal = 0;

    private int $daedalusId;

    #[ORM\ManyToOne(targetEntity: Daedalus::class)]
    private Daedalus $daedalus;

    public function __construct(RebelBaseConfig $rebelBaseConfig, int $daedalusId, bool $isContacting = false)
    {
        $this->rebelBaseConfig = $rebelBaseConfig;
        $this->daedalusId = $daedalusId;
        $this->isContacting = $isContacting;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isDecoded(): bool
    {
        return $this->signal >= 100;
    }

    public function isNotContacting(): bool
    {
        return $this->isContacting === false;
    }

    /**
     * @return ArrayCollection<int, AbstractModifierConfig>
     */
    public function getModifierConfigs(): ArrayCollection
    {
        return new ArrayCollection($this->rebelBaseConfig->getModifierConfigs()->toArray());
    }

    public function increaseDecodingProgress(int $amount): void
    {
        $this->signal += $amount;
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
