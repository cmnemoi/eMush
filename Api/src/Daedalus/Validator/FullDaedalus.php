<?php


namespace Mush\Daedalus\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FullDaedalus extends Constraint
{
    public const FULL_DAEDALUS_ERROR = 'full_daedalus_error';

    public string $message = 'This daedalus is full';
}