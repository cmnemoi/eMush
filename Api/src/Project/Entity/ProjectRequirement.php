<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Project\Dto\ProjectRequirementConfigDto;
use Mush\Project\Enum\ProjectRequirementName;
use Mush\Project\Enum\ProjectRequirementType;

#[ORM\Entity]
class ProjectRequirement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    private string $type = '';

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    private string $target = '';

    public function __construct(
        ProjectRequirementName $name,
        ProjectRequirementType $type,
        string $target = '',
    ) {
        $this->name = $name->value;
        $this->type = $type->value;
        $this->target = $target;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function updateFromConfigData(ProjectRequirementConfigDto $configData): void
    {
        $this->name = $configData->name->value;
        $this->type = $configData->type->value;
        $this->target = $configData->target;
    }

    public function isSatisfiedFor(Player $player)
    {
        $daedalus = $player->getDaedalus();
        $laboratory = $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);

        return match ($this->type) {
            ProjectRequirementType::CHUN_IN_LABORATORY->value => $laboratory->isChunIn(),
            ProjectRequirementType::ITEM_IN_LABORATORY->value => $player->canReachEquipmentByName($this->getTargetOrThrow()),
            ProjectRequirementType::ITEM_IN_PLAYER_INVENTORY->value => $player->hasEquipmentByName($this->getTargetOrThrow()),
            ProjectRequirementType::MUSH_PLAYER_DEAD->value => $daedalus->hasAnyMushDied(),
            default => throw new \LogicException("Unknown project requirement type: {$this->type}"),
        };
    }

    private function getTargetOrThrow(): string
    {
        if ('' === $this->target) {
            throw new \LogicException("Target is mandatory for {$this->name} because is of type {$this->type}");
        }

        return $this->target;
    }
}
