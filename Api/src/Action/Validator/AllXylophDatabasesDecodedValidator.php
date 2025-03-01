<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class AllXylophDatabasesDecodedValidator extends ConstraintValidator
{
    public function __construct(private readonly XylophRepositoryInterface $xylophRepository) {}

    public function validate($value, Constraint $constraint)
    {
        $action = $value;

        if (!$action instanceof AbstractAction) {
            throw new UnexpectedTypeException($action, AbstractAction::class);
        }

        if (!$constraint instanceof AllXylophDatabasesDecoded) {
            throw new UnexpectedTypeException($constraint, AllXylophDatabasesDecoded::class);
        }

        $daedalusId = $action->getPlayer()->getDaedalus()->getId();

        if ($this->xylophRepository->areAllXylophDatabasesDecoded($daedalusId)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
