<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Equipment\Entity\GameEquipment;
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

        /** @var GameEquipment $terminal */
        $terminal = $action->getTarget();
        $crewLock = $player->getDaedalus()->getNeron()->getCrewLock()->value;

        $skillNeeded = match ($crewLock) {
            NeronCrewLockEnum::PILOTING->value => SkillEnum::PILOT,
            NeronCrewLockEnum::PROJECTS->value => SkillEnum::CONCEPTOR,
            default => SkillEnum::null,
        };
        $restrictedTerminals = $constraint->terminals;

        $playerDoesNotWantToAccessRestrictedTerminal = \count($restrictedTerminals) === 0;
        $playerWantsToAccessRestrictedTerminal = \count($restrictedTerminals) > 0;
        $currentTerminalIsInTheList = \in_array($terminal->getName(), $restrictedTerminals, strict: true);

        if (
            $player->hasSkill($skillNeeded) === false
            && ($playerDoesNotWantToAccessRestrictedTerminal || $playerWantsToAccessRestrictedTerminal && $currentTerminalIsInTheList)
        ) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
