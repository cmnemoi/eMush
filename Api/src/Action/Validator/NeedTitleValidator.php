<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Enum\ItemEnum;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class NeedTitleValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof NeedTitle) {
            throw new UnexpectedTypeException($constraint, NeedTitle::class);
        }

        $player = $value->getPlayer();
        $daedalus = $player->getDaedalus();

        if ($player->hasTitle($constraint->title)) {
            return;
        }

        if ($constraint->allowIfNoPlayerHasTheTitle && $this->noPlayerHasTitleInDaedalus($constraint->title, $daedalus)) {
            return;
        }

        if ($value->getActionName() === ActionEnum::COM_MANAGER_ANNOUNCEMENT->toString() && $player->hasEquipmentByName(ItemEnum::MEGAPHONE)) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }

    private function noPlayerHasTitleInDaedalus(string $title, Daedalus $daedalus): bool
    {
        foreach ($daedalus->getAlivePlayers() as $alivePlayer) {
            if ($alivePlayer->hasTitle($title)) {
                return false;
            }
        }

        return true;
    }
}
