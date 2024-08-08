<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\Door;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class GuardianValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Guardian) {
            throw new UnexpectedTypeException($constraint, Guardian::class);
        }

        if ($this->playerRoomHasAGuardian($value)
            && $this->playerDoesNotWantToGoToPreviousRoom($value)
            && $this->playerIsNotSneak($value)
        ) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function playerRoomHasAGuardian(AbstractAction $value): bool
    {
        $player = $value->getPlayer();
        $playerRoom = $player->getPlace();

        return $playerRoom->hasAGuardian();
    }

    private function playerDoesNotWantToGoToPreviousRoom(AbstractAction $value): bool
    {
        /** @var Door $door */
        $door = $value->getActionProvider();
        $player = $value->getPlayer();
        $playerRoom = $player->getPlace();
        $targetRoom = $door->getOtherRoom($playerRoom);
        $previousRoom = $player->getStatusByName(PlayerStatusEnum::PREVIOUS_ROOM)?->getTarget();

        return $previousRoom?->notEquals($targetRoom) ?? false;
    }

    private function playerIsNotSneak(AbstractAction $value): bool
    {
        return $value->getPlayer()->hasSkill(SkillEnum::SNEAK) === false;
    }
}
