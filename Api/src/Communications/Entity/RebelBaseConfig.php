<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity]
class RebelBaseConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $key;

    #[ORM\Column(type: 'string', nullable: false, enumType: RebelBaseEnum::class, options: ['default' => RebelBaseEnum::NULL])]
    private RebelBaseEnum $name;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $contactOrder;

    #[ORM\ManyToMany(targetEntity: AbstractModifierConfig::class)]
    private Collection $modifierConfigs;

    #[ORM\ManyToOne(targetEntity: StatusConfig::class)]
    private ?StatusConfig $statusConfig;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $moral;

    public function __construct(string $key, RebelBaseEnum $name, int $contactOrder, ArrayCollection $modifierConfigs, ?StatusConfig $statusConfig, int $moral)
    {
        $this->key = $key;
        $this->name = $name;
        $this->contactOrder = $contactOrder;
        $this->modifierConfigs = $modifierConfigs;
        $this->statusConfig = $statusConfig;
        $this->moral = $moral;
    }

    public static function createNull(): self
    {
        return new self('', RebelBaseEnum::NULL, 0, new ArrayCollection(), null, 0);
    }

    public function getName(): RebelBaseEnum
    {
        return $this->name;
    }

    public function getContactOrder(): int
    {
        return $this->contactOrder;
    }

    public function getModifierConfigs(): Collection
    {
        return $this->modifierConfigs;
    }

    public function getStatusConfig(): ?StatusConfig
    {
        return $this->statusConfig;
    }

    public function getMoral(): int
    {
        return $this->moral;
    }

    public function update(self $rebelBaseConfig): void
    {
        $this->key = $rebelBaseConfig->key;
        $this->name = $rebelBaseConfig->name;
        $this->contactOrder = $rebelBaseConfig->contactOrder;
        $this->modifierConfigs = $rebelBaseConfig->modifierConfigs;
        $this->statusConfig = $rebelBaseConfig->statusConfig;
    }
}
