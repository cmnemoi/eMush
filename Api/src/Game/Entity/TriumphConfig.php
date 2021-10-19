<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class TriumphConfig.
 *
 * @ORM\Entity()
 * @ORM\Table(name="triumph_config")
 */
class TriumphConfig implements ConfigInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig", inversedBy="triumphConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $triumph = 0;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isAllCrew = false;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $team;

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): self
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTriumph(): int
    {
        return $this->triumph;
    }

    public function setTriumph(int $triumph): self
    {
        $this->triumph = $triumph;

        return $this;
    }

    public function isAllCrew(): bool
    {
        return $this->isAllCrew;
    }

    public function setIsAllCrew(bool $isAllCrew): self
    {
        $this->isAllCrew = $isAllCrew;

        return $this;
    }

    public function getTeam(): string
    {
        return $this->team;
    }

    public function setTeam(string $team): self
    {
        $this->team = $team;

        return $this;
    }
}
