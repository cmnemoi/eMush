<?php

namespace Mush\Daedalus\Validator;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\GameConfigService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FullDaedalusValidator extends ConstraintValidator
{
    private GameConfigService $gameConfigService;

    public function __construct(GameConfigService $gameConfigService)
    {
        $this->gameConfigService = $gameConfigService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof Daedalus) {
            throw new \InvalidArgumentException();
        }

        if (!$constraint instanceof FullDaedalus) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\FullDaedalus');
        }

        if ($value->getPlayers()->count() >= $this->gameConfigService->getConfig()->getMaxPlayer()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(FullDaedalus::FULL_DAEDALUS_ERROR)
                ->addViolation()
            ;
        }
    }
}
