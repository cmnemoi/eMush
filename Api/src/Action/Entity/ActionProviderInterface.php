<?php

declare(strict_types=1);

namespace Mush\Action\Entity;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;

/**
 * This interface regroup entities that can propose an action
 * It allows to easily find what equipment/status/player/skill granted the action
 * It allows to easily find if the action is relying on a charge status.
 *
 * Base action providers are : Status, Player, GameEquipment, Skill
 *
 * This interface also applies to secondary action provider such as Place
 * those include their own actions but also actions of their status/skills...
 */
interface ActionProviderInterface
{
    public function getUsedCharge(ActionEnum $actionName): ?ChargeStatus;

    public function getOperationalStatus(ActionEnum $actionName): ActionProviderOperationalStateEnum;

    public function getClassName(): string;

    public function getId(): ?int;

    public function getProvidedActions(ActionHolderEnum $actionTarget, array $actionRanges): Collection;

    public function canPlayerReach(Player $player): bool;

    public function getLogKey(): string;

    public function getLogName(): string;
}
