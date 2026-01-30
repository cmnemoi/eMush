<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class MaxLengthConstraint extends ClassConstraint
{
    public string $message = 'Text length must be less or equal to the limit.';
    public string $parameterName;
    public int $maxLength;
}
