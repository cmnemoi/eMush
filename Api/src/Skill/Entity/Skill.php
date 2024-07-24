<?php

declare(strict_types=1);

namespace Mush\Skill\Entity;

use Doctrine\ORM\Mapping as ORM;
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

    public function __construct(SkillConfig $skillConfig)
    {
        $this->skillConfig = $skillConfig;
    }

    public static function createNull(): self
    {
        return new self(new SkillConfig());
    }

    public function getName(): SkillName
    {
        return $this->skillConfig->getName();
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
