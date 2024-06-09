<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
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

        $skillNeeded = $this->getSkillNeeded($crewLock);
        $restrictedTerminals = $this->getRestrictedTerminals($crewLock);

        if (
            $player->hasSkill($skillNeeded) === false
            && $this->isTerminalRestricted($terminal, $restrictedTerminals)
        ) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function getSkillNeeded(string $crewLock): string
    {
        return match ($crewLock) {
            NeronCrewLockEnum::PILOTING->value => SkillEnum::PILOT,
            NeronCrewLockEnum::PROJECTS->value => SkillEnum::CONCEPTOR,
            default => SkillEnum::null,
        };
    }

    private function getRestrictedTerminals(string $crewLock): array
    {
        return match ($crewLock) {
            NeronCrewLockEnum::PILOTING->value => EquipmentEnum::getPatrolShips()->toArray(),
            NeronCrewLockEnum::PROJECTS->value => EquipmentEnum::getProjectTerminals()->toArray(),
            default => [],
        };
    }

    private function isTerminalRestricted(GameEquipment $terminal, array $restrictedTerminals): bool
    {
        return \in_array($terminal->getName(), $restrictedTerminals, true);
    }
}
