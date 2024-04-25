<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\UseCase\CreateProjectFromConfigForDaedalusUseCase;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class for actions that can be performed by admins.
 *
 * @Route(path="/admin/actions")
 */
final class AdminActionsController extends AbstractFOSRestController
{
    public function __construct(
        private CreateProjectFromConfigForDaedalusUseCase $createProjectFromConfigForDaedalusUseCase,
        private DaedalusRepository $daedalusRepository,
    ) {}

    /**
     * Create all projects for on-going Daedaluses.
     *
     * @OA\Tag (name="Admin")
     *
     * @Security (name="Bearer")
     *
     * @Rest\Post(path="/create-all-projects-for-on-going-daedaluses")
     */
    public function createAllProjectsForDaedaluses(): View
    {
        /** @var array<int, Daedalus> $onGoingDaedaluses */
        $onGoingDaedaluses = $this->daedalusRepository->findNonFinishedDaedaluses();

        foreach ($onGoingDaedaluses as $daedalus) {
            /** @var ProjectConfig $projectConfig */
            foreach ($daedalus->getProjectConfigs() as $projectConfig) {
                $this->createProjectFromConfigForDaedalusUseCase->execute($projectConfig, $daedalus);
            }
        }

        return $this->view(['detail' => 'Projects created successfully.'], Response::HTTP_OK);
    }
}
