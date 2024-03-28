<?php

namespace Mush\Communication\Validator;

use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Enum\ChannelScopeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MessageParentValidator extends ConstraintValidator
{   
    private const array ACCEPTED_SCOPES = [
        ChannelScopeEnum::PUBLIC,
        ChannelScopeEnum::FAVORITES,
    ];

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof CreateMessage) {
            throw new \UnexpectedValueException($value);
        }

        if (!$constraint instanceof MessageParent) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\MessageParent');
        }

        $channel = $value->getChannel();
        if (!in_array(ChannelScopeEnum::PUBLIC, self::ACCEPTED_SCOPES) && $value->getParent()) {
            $this->context
                    ->buildViolation($constraint->message)
                    ->setCode(MessageParent::PARENT_CANNOT_BE_SET)
                    ->addViolation()
            ;
        }
    }
}
