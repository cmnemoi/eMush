<?php

namespace Mush\Daedalus\Validator;

use Mush\Daedalus\Entity\Dto\DaedalusCreateRequest;
use Mush\Daedalus\Repository\DaedalusInfoRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueNameValidator extends ConstraintValidator
{
    private DaedalusInfoRepository $daedalusRepository;

    public function __construct(DaedalusInfoRepository $daedalusRepository)
    {
        $this->daedalusRepository = $daedalusRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof DaedalusCreateRequest) {
            throw new \InvalidArgumentException();
        }

        if (!$constraint instanceof UniqueName) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\UniqueName');
        }

        $name = $value->getName();

        if ($this->daedalusRepository->findOneBy(['name' => $name]) === null) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(UniqueName::DAEDALUS_NAME_ALREADY_USED)
                ->addViolation();
        }
    }
}
