<?php

declare(strict_types=1);

namespace Mush\Project\Enum;

enum ProjectType: string
{
    case NULL = '';
    case NERON_PROJECT = 'neron_project';
    case PILGRED = 'pilgred';
    case RESEARCH = 'research';
}
