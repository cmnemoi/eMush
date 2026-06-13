<?php

declare(strict_types=1);

namespace Mush\Daedalus\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class StartingDaedalus extends Constraint
{
    public const STARTING_DAEDALUS_ERROR = 'starting_daedalus_error';

    public string $message = 'This daedalus cannot accept new players';
}
