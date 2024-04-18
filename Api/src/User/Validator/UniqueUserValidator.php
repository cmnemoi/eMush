<?php

namespace Mush\User\Validator;

use Mush\Player\Entity\Dto\PlayerCreateRequest;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueUserValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof PlayerCreateRequest) {
            throw new \InvalidArgumentException();
        }

        if (!$constraint instanceof UniqueUser) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\UniqueUser ');
        }

        $daedalus = $value->getDaedalus();
        $user = $value->getUser();

        if ($daedalus !== null
            && $user !== null
            && $daedalus->getPlayers()->filter(static fn (Player $player) => $player->getUser() === $user)->count() > 0
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(UniqueUser::USER_IS_ALREADY_ON_DAEDALUS)
                ->atPath('user')
                ->addViolation();
        }
    }
}
