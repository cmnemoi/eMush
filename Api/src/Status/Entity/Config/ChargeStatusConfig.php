<?php

declare(strict_types=1);

namespace Mush\Status\Entity\Config;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['status_config_read']],
    denormalizationContext: ['groups' => ['status_config_write']],
    paginationItemsPerPage: 25,
    security: 'is_granted("ROLE_MODERATOR")',
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_MODERATOR")',
            filters: ['default.search_filter', 'default.order_filter'],
        ),
        new Get(
            security: 'is_granted("ROLE_MODERATOR")',
        ),
        new Put(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
)]
class ChargeStatusConfig extends StatusConfig
{
    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['status_config_read', 'status_config_write'])]
    private string $chargeVisibility = VisibilityEnum::PUBLIC;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['status_config_read', 'status_config_write'])]
    private string $chargeStrategy = ChargeStrategyTypeEnum::NONE;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['status_config_read', 'status_config_write'])]
    private ?int $maxCharge = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Groups(['status_config_read', 'status_config_write'])]
    private int $startCharge = 0;

    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['status_config_read', 'status_config_write'])]
    private array $dischargeStrategies = [ChargeStrategyTypeEnum::NONE];

    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Groups(['status_config_read', 'status_config_write'])]
    private bool $autoRemove = false;

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
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

        $statusConfig->setId(crc32(serialize($statusConfig)));

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

    public function getMaxChargeOrThrow(): int
    {
        return $this->maxCharge ?? throw new \RuntimeException("{$this->getName()} does not have set max charge.");
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
}
