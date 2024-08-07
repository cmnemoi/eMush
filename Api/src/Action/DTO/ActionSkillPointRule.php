<?php

namespace Mush\Action\DTO;

use Mush\Skill\Enum\SkillEnum;

/**
 * This class permits to pass object instead of arbitrary array named.
 * Use this as an example of usage we can take for further possibility where
 * random arrays are used. It may look over-engineered, and it is probably, but it worth it.
 *
 * @author RSickenberg (@haux49)
 *
 * @version 1.0.0
 */
final readonly class ActionSkillPointRule
{
    public function __construct(
        public string $name,
        public SkillEnum $skill,
        public array $actionTypes
    ) {}
}
