<?php

declare(strict_types=1);

namespace Mush\Player\Dto;

use Mush\Skill\Enum\SkillEnum;
use Symfony\Component\HttpFoundation\Request;

final readonly class ChooseSkillDto
{
    public SkillEnum $skill;

    public function __construct(
        Request $request
    ) {
        $this->skill = SkillEnum::from($request->toArray()['skill']);
    }
}
