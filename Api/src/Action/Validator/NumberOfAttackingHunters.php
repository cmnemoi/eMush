<?php

namespace Mush\Action\Validator;

class NumberOfAttackingHunters extends ClassConstraint
{
    public const LESS_THAN = 'less_than';
    public const GREATER_THAN = 'greater_than';
    public const EQUAL = 'equal';

    public string $mode;
    public int $number;

    public string $message = 'the number of attacking hunters required is not matched';
}
