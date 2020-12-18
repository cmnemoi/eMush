<?php

namespace Mush\Communication\Validator;

use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Enum\ChannelScopeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

class MessageParentValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof CreateMessage) {
            throw new UnexpectedValueException($value, CreateMessage::class);
        }

        $channel = $value->getChannel();
        if ($channel && $channel->getScope() !== ChannelScopeEnum::PUBLIC && $value->getParent()) {
            $this->context
                    ->buildViolation($constraint->message)
                    ->setCode(MessageParent::PARENT_CANNOT_BE_SET)
                    ->addViolation()
                ;
        }
    }
}
