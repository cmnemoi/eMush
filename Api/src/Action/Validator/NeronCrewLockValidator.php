<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Game\Enum\SkillEnum;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class NeronCrewLockValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        $action = $value;

        if (!$action instanceof AbstractAction) {
            throw new UnexpectedTypeException($action, AbstractAction::class);
        }

        if (!$constraint instanceof NeronCrewLock) {
            throw new UnexpectedTypeException($constraint, NeronCrewLock::class);
        }

        $player = $action->getPlayer();
        $crewLock = $player->getDaedalus()->getNeron()->getCrewLock()->value;

        $skillNeeded = match ($crewLock) {
            NeronCrewLockEnum::PILOTING->value => SkillEnum::PILOT,
            NeronCrewLockEnum::PROJECTS->value => SkillEnum::CONCEPTOR,
            default => SkillEnum::null,
        };

        if ($player->hasSkill($skillNeeded) === false) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
