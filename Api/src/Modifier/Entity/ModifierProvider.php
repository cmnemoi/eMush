<?php

declare(strict_types=1);

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\XylophEntry;
use Mush\Disease\Entity\PlayerDisease;
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
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: GameEquipment::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?GameEquipment $gameEquipment = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: Status::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Status $status = null;

    #[ORM\ManyToOne(targetEntity: RebelBase::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?RebelBase $rebelBase = null;

    #[ORM\ManyToOne(targetEntity: XylophEntry::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?XylophEntry $xylophEntry = null;

    #[ORM\ManyToOne(targetEntity: PlayerDisease::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?PlayerDisease $playerDisease = null;

    #[ORM\OneToOne(mappedBy: 'modifierProvider', targetEntity: GameModifier::class, cascade: ['remove'])]
    private ?GameModifier $gameModifier = null;

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
        if ($this->xylophEntry) {
            return $this->xylophEntry;
        }
        if ($this->playerDisease) {
            return $this->playerDisease;
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
        $this->xylophEntry = null;
        $this->playerDisease = null;

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
        if ($modifierProvider instanceof XylophEntry) {
            $this->xylophEntry = $modifierProvider;

            return $this;
        }
        if ($modifierProvider instanceof PlayerDisease) {
            $this->playerDisease = $modifierProvider;

            return $this;
        }

        throw new LogicException('this class is not a modifierProvider');
    }

    public function setGameModifier(GameModifier $gameModifier): static
    {
        $this->gameModifier = $gameModifier;

        return $this;
    }
}
