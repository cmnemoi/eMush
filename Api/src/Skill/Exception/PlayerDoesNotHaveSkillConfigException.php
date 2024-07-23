<?php

declare(strict_types=1);

namespace Mush\Skill\Exception;

use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillName;

final class PlayerDoesNotHaveSkillConfigException extends \RuntimeException
{
    public function __construct(Player $player, SkillName $skillName)
    {
        parent::__construct(sprintf('Player "%s" does not have "%s" skill config', $player->getId(), $skillName->value));
    }
}
