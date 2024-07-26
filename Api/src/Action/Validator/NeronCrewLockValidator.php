<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\AbstractAction;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Skill\Enum\SkillEnum;
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

    private function getSkillNeeded(string $crewLock): SkillEnum
    {
        return match ($crewLock) {
            NeronCrewLockEnum::PILOTING->value => SkillEnum::PILOT,
            NeronCrewLockEnum::PROJECTS->value => SkillEnum::CONCEPTOR,
            default => SkillEnum::NULL,
        };
    }

    private function getRestrictedTerminals(string $crewLock): ArrayCollection
    {
        return match ($crewLock) {
            NeronCrewLockEnum::PILOTING->value => EquipmentEnum::getPilotingCrewLockRestrictedTerminals(),
            NeronCrewLockEnum::PROJECTS->value => EquipmentEnum::getNeronProjectTerminals(),
            default => new ArrayCollection(),
        };
    }

    private function isTerminalRestricted(GameEquipment $terminal, ArrayCollection $restrictedTerminals): bool
    {
        return $restrictedTerminals->contains($terminal->getName());
    }
}
