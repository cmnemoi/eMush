<?php

namespace Mush\Daedalus\Validator;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\GameConfigService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StartingDaedalusValidator extends ConstraintValidator
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

        if (!$constraint instanceof StartingDaedalus) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\StartingDaedalus');
        }

        if ($value->getGameStatus() !== GameStatusEnum::STARTING) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(StartingDaedalus::STARTING_DAEDALUS_ERROR)
                ->addViolation()
            ;
        }
    }
}
