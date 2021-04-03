<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FullHealthValidator extends ConstraintValidator
{
    private PlayerVariableServiceInterface $playerVariableService;

    public function __construct(PlayerVariableServiceInterface $playerVariableService)
    {
        $this->playerVariableService = $playerVariableService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        if (!$constraint instanceof FullHealth) {
            throw new UnexpectedTypeException($constraint, Reach::class);
        }

        $player = match ($constraint->target) {
            FullHealth::PARAMETER => $value->getParameter(),
            FullHealth::PLAYER => $value->getPlayer()
        };

        if (!$player instanceof Player) {
            throw new UnexpectedTypeException($constraint, Player::class);
        }
        $maxHealthPoint = $this->playerVariableService->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_HEALTH_POINT);

        if ($player->getHealthPoint() === $maxHealthPoint) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
