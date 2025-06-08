<?php

declare(strict_types=1);

namespace Mush\Project\Enum;

enum ProjectType: string
{
    case NULL = '';
    case NERON_PROJECT = 'neron_project';
    case PILGRED = 'pilgred';
    case RESEARCH = 'research';

    public function toString(): string
    {
        return $this->value;
    }

    public static function fromCategory(string $category): self
    {
        return match ($category) {
            'neronProjects' => self::NERON_PROJECT,
            'pilgredProjects' => self::PILGRED,
            'researchProjects' => self::RESEARCH,
        };
    }
}
