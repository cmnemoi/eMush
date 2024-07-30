<?php

declare(strict_types=1);

namespace Mush\Skill\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\SkillPointsEnum;

#[ORM\Entity]
class Skill implements ActionProviderInterface
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

    public static function createByNameForPlayer(SkillEnum $skill, Player $player): self
    {
        return new self(new SkillConfig($skill), $player);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): SkillEnum
    {
        return $this->skillConfig->getName();
    }

    public function getNameAsString(): string
    {
        return $this->getName()->value;
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

    public function getSkillPointConfigOrNull(): ?ChargeStatusConfig
    {
        return $this->skillConfig->getSkillPointsConfig();
    }

    public function getSkillPoints(): int
    {
        return $this->player->getChargeStatusByName(
            SkillPointsEnum::fromSkill($this)->toString()
        )?->getCharge() ?? 0;
    }

    public function getSkillPointsName(): string
    {
        return $this->getName()->getSkillPointsName();
    }

    public function isMushSkill(): bool
    {
        return $this->getName()->isMushSkill();
    }

    public function isHumanSkill(): bool
    {
        return $this->isMushSkill() === false;
    }

    public function getUsedCharge(ActionEnum $actionName): null
    {
        return null;
    }

    public function getOperationalStatus(ActionEnum $actionName): ActionProviderOperationalStateEnum
    {
        return ActionProviderOperationalStateEnum::OPERATIONAL;
    }

    public function getClassName(): string
    {
        return static::class;
    }

    public function getProvidedActions(ActionHolderEnum $actionTarget, array $actionRanges): Collection
    {
        $actions = [];

        foreach ($this->skillConfig->getActionConfigs() as $actionConfig) {
            if (
                $actionConfig->getDisplayHolder() === $actionTarget
                && \in_array($actionConfig->getRange(), $actionRanges, true)
            ) {
                $action = new Action();
                $action->setActionProvider($this)->setActionConfig($actionConfig);

                $actions[] = $action;
            }
        }

        return new ArrayCollection($actions);
    }

    public function canPlayerReach(Player $player): bool
    {
        return $player->canPlayerReach($this->player);
    }
}
