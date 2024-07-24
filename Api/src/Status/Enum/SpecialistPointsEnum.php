<?php

declare(strict_types=1);

namespace Mush\Status\Enum;

enum SpecialistPointsEnum: string
{
    case CONCEPTOR_POINTS = 'conceptor_points';
    case SHOOTER_POINTS = 'shooter_points';
    case TECHNICIAN_POINTS = 'technician_points';
}
