<?php

namespace Mush\Action\Validator;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

abstract class AbstractActionValidator extends ConstraintValidator
{
    protected LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    abstract public function validate($value, Constraint $constraint): void;

}