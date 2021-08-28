<?php

namespace Mush\Status\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;
use Mush\RoomLog\Enum\VisibilityEnum;

/**
 * Class StatusConfig.
 *
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "status_config" = "Mush\Status\Entity\Config\ChargeConfig",
 *     "charge_status_config" = "Mush\Status\Entity\Config\ChargeStatusConfig",
 * })
 */
class StatusConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected string $name;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected string $visibility = VisibilityEnum::PUBLIC;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private array $applyToEquipments = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): StatusConfig
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return static
     */
    public function setName(string $name): StatusConfig
    {
        $this->name = $name;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @return static
     */
    public function setVisibility(string $visibility): StatusConfig
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getApplyToEquipments(): array
    {
        return $this->getApplyToEquipments();
    }

    /**
     * @return static
     */
    public function setApplyToEquipments(array $applyToEquipments): StatusConfig
    {
        $this->applyToEquipments = $applyToEquipments;

        return $this;
    }
}
