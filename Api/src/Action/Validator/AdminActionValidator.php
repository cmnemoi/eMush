<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class AdminActionValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AdminAction::class);
        }

        if (!$constraint instanceof AdminAction) {
            throw new UnexpectedTypeException($constraint, AdminAction::class);
        }

        $action = $value;

        if ($action->isNotAdminAction()) {
            return;
        }

        $user = $action->getPlayer()->getUser();

        if ($user->isNotAdmin() || $this->isNotDevelopmentEnvironment()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function isNotDevelopmentEnvironment(): bool
    {
        if (!isset($_ENV['APP_ENV'])) {
            throw new \RuntimeException('APP_ENV is not set.');
        }

        return $_ENV['APP_ENV'] !== 'dev';
    }
}
