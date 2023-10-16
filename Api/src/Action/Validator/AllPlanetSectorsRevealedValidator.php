<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Service\PlanetServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class AllPlanetSectorsRevealedValidator extends ConstraintValidator
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

        if (!$constraint instanceof AllPlanetSectorsRevealed) {
            throw new UnexpectedTypeException($constraint, AllPlanetSectorsRevealed::class);
        }

        $planet = $this->getPlanetFromActionParameters($value);

        if ($planet->getSectors()->count() === $planet->getRevealedSectors()->count()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function getPlanetFromActionParameters(AbstractAction $action): Planet
    {
        $actionParameters = $action->getParameters();
        if (!isset($actionParameters['planet'])) {
            throw new \RuntimeException('Planet not found in action parameters');
        }

        $planetId = $actionParameters['planet'];
        $planet = $this->planetService->findById($planetId);

        if (!$planet) {
            throw new \RuntimeException("Planet $planetId not found");
        }

        return $planet;
    }
}
