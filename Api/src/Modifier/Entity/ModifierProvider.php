<?php

declare(strict_types=1);

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\Entity\RebelBase;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Status\Entity\Status;
use Symfony\Component\Validator\Exception\LogicException;

#[ORM\Entity]
#[ORM\Table(name: 'modifier_provider')]
class ModifierProvider
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: GameEquipment::class)]
    private ?GameEquipment $gameEquipment = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: Status::class)]
    private ?Status $status = null;

    #[ORM\ManyToOne(targetEntity: RebelBase::class)]
    private ?RebelBase $rebelBase = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModifierProvider(): ModifierProviderInterface
    {
        if ($this->player) {
            return $this->player;
        }
        if ($this->gameEquipment) {
            return $this->gameEquipment;
        }
        if ($this->project) {
            return $this->project;
        }
        if ($this->status) {
            return $this->status;
        }
        if ($this->rebelBase) {
            return $this->rebelBase;
        }

        throw new LogicException("this modifier don't have any valid provider");
    }

    public function setModifierProvider(ModifierProviderInterface $modifierProvider): static
    {
        $this->gameEquipment = null;
        $this->project = null;
        $this->player = null;
        $this->status = null;
        $this->rebelBase = null;

        if ($modifierProvider instanceof Player) {
            $this->player = $modifierProvider;

            return $this;
        }
        if ($modifierProvider instanceof GameEquipment) {
            $this->gameEquipment = $modifierProvider;

            return $this;
        }
        if ($modifierProvider instanceof Project) {
            $this->project = $modifierProvider;

            return $this;
        }
        if ($modifierProvider instanceof Status) {
            $this->status = $modifierProvider;

            return $this;
        }
        if ($modifierProvider instanceof RebelBase) {
            $this->rebelBase = $modifierProvider;

            return $this;
        }

        throw new LogicException('this class is not a modifierProvider');
    }
}
