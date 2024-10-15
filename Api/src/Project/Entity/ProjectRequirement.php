<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectRequirementType;

#[ORM\Entity]
class ProjectRequirement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    private string $type = '';

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    private string $target = '';

    public function __construct(
        string $name,
        string $type,
        ?string $target = '',
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->target = $target;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTargetOrThrow(): string
    {
        if (null === $this->target) {
            throw new \LogicException("Target is mandatory for {$this->name} because is of type {$this->type}");
        }

        return $this->target;
    }

    public function updateFromConfigData(array $configData): void
    {
        $this->name = $configData['name'];
        $this->type = $configData['type'];
        if (isset($configData['target'])) {
            $this->target = $configData['target'];
        }
    }

    public function isSatisfiedFor(Player $player)
    {
        $daedalus = $player->getDaedalus();
        $laboratory = $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);

        switch ($this->type) {
            case ProjectRequirementType::CHUN_IN_LABORATORY:
                return $laboratory->isChunIn();

            case ProjectRequirementType::ITEM_IN_LABORATORY:
                return $laboratory->hasEquipmentByName($this->getTargetOrThrow()) || $player->hasEquipmentByName($this->getTargetOrThrow());

            case ProjectRequirementType::ITEM_IN_PLAYER_INVENTORY:
                return $player->hasEquipmentByName($this->getTargetOrThrow());

            case ProjectRequirementType::MUSH_PLAYER_DEAD:
                return $daedalus->hasAnyMushDied();

            default:
                throw new \LogicException("Unknown project requirement type: {$this->type}");
        }
    }
}
