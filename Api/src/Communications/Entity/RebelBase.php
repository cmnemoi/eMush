<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity]
class RebelBase implements ModifierProviderInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: RebelBaseConfig::class)]
    private RebelBaseConfig $rebelBaseConfig;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $contactStartDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $contactEndDate;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $signal = 0;

    private int $daedalusId;

    #[ORM\ManyToOne(targetEntity: Daedalus::class)]
    private Daedalus $daedalus;

    public function __construct(RebelBaseConfig $config, int $daedalusId, ?\DateTimeImmutable $contactStartDate = null)
    {
        $this->rebelBaseConfig = $config;
        $this->daedalusId = $daedalusId;
        $this->contactStartDate = $contactStartDate;
        $this->contactEndDate = null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): RebelBaseEnum
    {
        return $this->rebelBaseConfig->getName();
    }

    public function getContactOrder(): int
    {
        return $this->rebelBaseConfig->getContactOrder();
    }

    public function getSignal(): int
    {
        return $this->signal;
    }

    public function getContactStartDateOrThrow(): \DateTime
    {
        if ($this->contactDidNotStart()) {
            throw new \RuntimeException("Rebel base {$this->getName()->toString()} did not start contact");
        }

        return \DateTime::createFromImmutable($this->getContactStartDate());
    }

    public function isDecoded(): bool
    {
        return $this->signal >= 100;
    }

    public function isNotContacting(): bool
    {
        return $this->contactDidNotStart() || $this->contactEnded();
    }

    public function isContacting(): bool
    {
        return !$this->isNotContacting() && !$this->isDecoded();
    }

    public function isLost(): bool
    {
        return $this->contactEnded() && !$this->isDecoded();
    }

    public function increaseDecodingProgress(int $amount): void
    {
        $this->signal += $amount;
    }

    public function triggerContact(?\DateTime $contactDate = null): void
    {
        $this->contactStartDate = \DateTimeImmutable::createFromMutable($contactDate ?? $this->daedalus->getCycleStartedAtOrThrow());
    }

    public function endContact(): void
    {
        $this->contactEndDate = $this->now();
    }

    /**
     * @return ArrayCollection<int, AbstractModifierConfig>
     */
    public function getModifierConfigs(): ArrayCollection
    {
        return new ArrayCollection($this->rebelBaseConfig->getModifierConfigs()->toArray());
    }

    public function getStatusConfig(): ?StatusConfig
    {
        return $this->rebelBaseConfig->getStatusConfig();
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

    public function contactEnded(): bool
    {
        return $this->contactEndDate !== null;
    }

    private function contactDidNotStart(): bool
    {
        return $this->contactStartDate === null;
    }

    private function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    private function getContactStartDate(): \DateTimeImmutable
    {
        return $this->contactStartDate ?? throw new \RuntimeException("Rebel base {$this->getName()->toString()} did not start contact");
    }
}
