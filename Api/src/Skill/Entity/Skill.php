<?php

declare(strict_types=1);

namespace Mush\Skill\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillName;

#[ORM\Entity]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: SkillConfig::class)]
    private SkillConfig $skillConfig;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private Player $player;

    public function __construct(SkillConfig $skillConfig, Player $player)
    {
        $this->skillConfig = $skillConfig;
        $this->player = $player;

        $player->addSkill($this);
    }

    public static function createNullForPlayer(Player $player): self
    {
        return new self(new SkillConfig(), $player);
    }

    public function getName(): SkillName
    {
        return $this->skillConfig->getName();
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return ArrayCollection<int, AbstractModifierConfig>
     */
    public function getModifierConfigs(): ArrayCollection
    {
        return $this->skillConfig->getModifierConfigs();
    }

    public function getCharge(): int
    {
        return 0;
    }

    public function isNull(): bool
    {
        return $this->getName() === SkillName::NULL;
    }
}
