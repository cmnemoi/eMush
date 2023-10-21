<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class AllPlanetSectorsVisitedValidator extends ConstraintValidator
{   
    private PlanetServiceInterface $planetService;

    public function __construct(PlanetServiceInterface $planetService)
    {
        $this->planetService = $planetService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AllPlanetSectorsVisited) {
            throw new UnexpectedTypeException($constraint, AllPlanetSectorsVisited::class);
        }
        
        $daedalus = $value->getPlayer()->getDaedalus();
        if (!$daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }

        $planets = $this->planetService->findAllByDaedalus($daedalus);
        if ($planets->count() !== 1) {
            throw new \RuntimeException('Daedalus should have only one planet if in orbit');
        }

        /** @var Planet $planet */
        $planet = $planets->first();

        if ($planet->getSectors()->count() === $planet->getVisitedSectors()->count()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
