<?php

declare(strict_types=1);

namespace Mush\Player\Dto;

use Mush\Skill\Enum\SkillName;
use Symfony\Component\HttpFoundation\Request;

final readonly class ChooseSkillDto
{
    public SkillName $skill;

    public function __construct(
        Request $request
    ) {
        $this->skill = SkillName::from($request->toArray()['skill']);
    }
}
