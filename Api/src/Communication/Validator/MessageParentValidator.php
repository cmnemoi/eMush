<?php

namespace Mush\Communication\Validator;

use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Enum\ChannelScopeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MessageParentValidator extends ConstraintValidator
{
    private const array ALLOWED_SCOPES = [ChannelScopeEnum::PUBLIC, ChannelScopeEnum::FAVORITES];

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof CreateMessage) {
            throw new \UnexpectedValueException($value);
        }

        if (!$constraint instanceof MessageParent) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\MessageParent');
        }

        $channel = $value->getChannel();
        if ($value->getParent() && !\in_array($channel->getScope(), self::ALLOWED_SCOPES, true)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(MessageParent::PARENT_CANNOT_BE_SET)
                ->addViolation();
        }
    }
}
