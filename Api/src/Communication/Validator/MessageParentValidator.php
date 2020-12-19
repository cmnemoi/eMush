<?php

namespace Mush\Communication\Validator;

use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Enum\ChannelScopeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use UnexpectedValueException;

class MessageParentValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof CreateMessage) {
            throw new UnexpectedValueException($value);
        }

        if (!$constraint instanceof MessageParent) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\MessageParent');
        }

        $channel = $value->getChannel();
        if ($channel->getScope() !== ChannelScopeEnum::PUBLIC && $value->getParent()) {
            $this->context
                    ->buildViolation($constraint->message)
                    ->setCode(MessageParent::PARENT_CANNOT_BE_SET)
                    ->addViolation()
                ;
        }
    }
}
