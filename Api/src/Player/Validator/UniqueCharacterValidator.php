<?php

namespace Mush\Player\Validator;

use Mush\Player\Entity\Dto\PlayerRequest;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueCharacterValidator extends ConstraintValidator
{
    private PlayerServiceInterface $playerService;

    public function __construct(PlayerServiceInterface $playerService)
    {
        $this->playerService = $playerService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof PlayerRequest) {
            throw new \InvalidArgumentException();
        }

        if (!$constraint instanceof UniqueCharacter) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\UniqueCharacter');
        }

        $daedalus = $value->getDaedalus();
        $character = $value->getCharacter();
        if ($daedalus !== null &&
            $character !== null &&
            $this->playerService->findOneByCharacter($character, $daedalus) !== null
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(UniqueCharacter::CHARACTER_IS_NOT_UNIQUE_ERROR)
                ->atPath('character')
                ->addViolation()
            ;
        }
    }
}
