<?php

declare(strict_types=1);

namespace Mush\Daedalus\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class FullDaedalus extends Constraint
{
    public const FULL_DAEDALUS_ERROR = 'full_daedalus_error';

    public string $message = 'This daedalus is full';
}
