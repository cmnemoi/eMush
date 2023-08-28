<?php

namespace Mush\Player\Validator;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Player\Entity\Dto\PlayerCreateRequest;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueCharacterValidator extends ConstraintValidator
{
    private DaedalusServiceInterface $daedalusService;

    public function __construct(DaedalusServiceInterface $daedalusService)
    {
        $this->daedalusService = $daedalusService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof PlayerCreateRequest) {
            throw new \InvalidArgumentException();
        }

        if (!$constraint instanceof UniqueCharacter) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\UniqueCharacter');
        }

        $daedalus = $value->getDaedalus();
        $character = $value->getCharacter();

        if ($daedalus !== null
            && !$daedalus->getPlayers()->filter(fn (Player $player) => $player->getName() === $character)->isEmpty()
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
