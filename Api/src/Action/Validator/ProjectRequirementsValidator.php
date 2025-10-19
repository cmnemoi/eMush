<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Communications\Collection\RebelBaseCollection;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Project\Entity\Project;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ProjectRequirementsValidator extends ConstraintValidator
{
    private RebelBaseRepositoryInterface $rebelBaseRepository;

    public function __construct(RebelBaseRepositoryInterface $rebelBaseRepository)
    {
        $this->rebelBaseRepository = $rebelBaseRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        $action = $value;

        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof ProjectRequirements) {
            throw new UnexpectedTypeException($constraint, ProjectFinished::class);
        }

        $player = $action->getPlayer();
        $project = $action->getTarget();
        if (!$project instanceof Project) {
            throw new UnexpectedTypeException($project, Project::class);
        }

        $rebelbases = new RebelBaseCollection($this->rebelBaseRepository->findAllDecodedRebelBases($player->getDaedalus()->getId()));

        if (!$project->isVisibleFor($player, $rebelbases)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
