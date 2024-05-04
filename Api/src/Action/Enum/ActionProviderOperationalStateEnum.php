<?php

declare(strict_types=1);

namespace Mush\Action\Enum;

enum ActionProviderOperationalStateEnum: string
{
    case OPERATIONAL = 'operational';
    case DISCHARGED = 'discharged';
    case BROKEN = 'broken';
}
