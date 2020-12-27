<?php

namespace Mush\Daedalus\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FinishedDaedalus extends Constraint
{
    public const FINISHED_DAEDALUS_ERROR = 'finished_daedalus_error';

    public string $message = 'This daedalus is already finished';
}
