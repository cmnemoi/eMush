<?php

declare(strict_types=1);

namespace Mush\Modifier\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;

/**
 * This interface regroup entities that can propose a modifier
 * It allows to easily find what equipment/status/player/skill granted the modifier
 * It allows to easily find if the modifier is relying on a charge status.
 *
 * Primary modifier providers are : Status, Player, GameEquipment, Skill
 *
 * This interface also applies to secondary modifier provider such as Place
 * those include their own actions but also actions of their status/skills...
 */
interface ModifierProviderInterface
{
    public function getUsedCharge(string $actionName): ?ChargeStatus;

    public function getOperationalStatus(string $actionName): ActionProviderOperationalStateEnum;

    public function getAllModifierConfigs(): ArrayCollection;

    public function getId(): ?int;
}
