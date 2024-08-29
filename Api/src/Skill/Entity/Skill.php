<?php

declare(strict_types=1);

namespace Mush\Skill\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Skill\ConfigData\SkillConfigData;
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
        return new self(skillConfig: new SkillConfig(), player: $player);
    }

    public static function createByNameForPlayer(SkillEnum $skill, Player $player): self
    {
        return new self(
            skillConfig: SkillConfig::createFromDto(SkillConfigData::getByName($skill)),
            player: $player
        );
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

    public function getConfig(): SkillConfig
    {
        return $this->skillConfig;
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

    public function getSkillPointConfig(): ChargeStatusConfig
    {
        return $this->skillConfig->getSkillPointsConfig();
    }

    public function getSkillPoints(): int
    {
        return $this->player->getChargeStatusByName(
            SkillPointsEnum::fromSkill($this)->toString()
        )?->getCharge() ?? 0;
    }

    public function hasSkillPoints(): bool
    {
        return $this->getSkillPoints() > 0;
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

    public function hasAnyActionTypes(array $expectedActionTypes): bool
    {
        $skillActionTypes = $this->getName()->getSkillActionTypes()->map(
            static fn (ActionTypeEnum $actionType) => $actionType->toString()
        )->toArray();

        return \count(array_intersect($skillActionTypes, $expectedActionTypes)) > 0;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->player->getDaedalus();
    }

    public function getUsedCharge(string $actionName): null
    {
        return null;
    }

    public function getOperationalStatus(string $actionName): ActionProviderOperationalStateEnum
    {
        return ActionProviderOperationalStateEnum::OPERATIONAL;
    }

    public function getClassName(): string
    {
        return self::class;
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

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::SKILL;
    }

    public function getLogName(): string
    {
        return $this->getNameAsString();
    }
}
