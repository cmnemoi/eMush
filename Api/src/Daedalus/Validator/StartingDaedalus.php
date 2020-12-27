<?php

namespace Mush\Daedalus\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StartingDaedalus extends Constraint
{
    public const STARTING_DAEDALUS_ERROR = 'starting_daedalus_error';

    public string $message = 'This daedalus cannot accept new players';
}
