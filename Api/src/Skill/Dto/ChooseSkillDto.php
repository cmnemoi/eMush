<?php

declare(strict_types=1);

namespace Mush\Skill\Dto;

use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Symfony\Component\HttpFoundation\Request;

final readonly class ChooseSkillDto
{
    public function __construct(public SkillEnum $skill, public Player $player) {}

    public static function createFromRequest(Request $request): self
    {
        return new self(
            SkillEnum::from((string) $request->request->get('skill')),
            $request->attributes->get('player')
        );
    }

    /**
     * @return array{0: SkillEnum, 1: Player}
     */
    public function toArgs(): array
    {
        return [$this->skill, $this->player];
    }
}
