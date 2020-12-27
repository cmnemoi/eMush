<?php

namespace Mush\Daedalus\Validator;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\GameConfigService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FinishedDaedalusValidator extends ConstraintValidator
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
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\FinishedDaedalus');
        }

        if ($value->getGameStatus() === GameStatusEnum::FINISHED ||
            $value->getGameStatus() === GameStatusEnum::CLOSED) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(FinishedDaedalus::FINISHED_DAEDALUS_ERROR)
                ->addViolation()
            ;
        }
    }
}
