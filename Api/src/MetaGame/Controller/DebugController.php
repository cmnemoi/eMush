<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\CycleServiceInterface;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DebugController for debug endpoints.
 *
 * @Route(path="/admin/debug")
 */
final class DebugController extends AbstractFOSRestController
{
    private CycleServiceInterface $cycleService;

    public function __construct(CycleServiceInterface $cycleService)
    {
        $this->cycleService = $cycleService;
    }

    /**
     * Force cycle change for a locked-up Daedalus.
     *
     * * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The daedalus id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Tag (name="Admin")
     *
     * @Security (name="Bearer")
     *
     * @Rest\Post(path="/unlock-daedalus/{id}", requirements={"id"="\d+"})
     */
    public function forceLockedDaedalusCycleChange(Daedalus $daedalus): View
    {
        $this->denyAccessIfNotAdmin();

        if (!$daedalus->isCycleChange() && !$daedalus->isExplorationChangingCycle()) {
            return $this->view(['error' => 'Daedalus is not on cycle change'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        return $this->view(['detail' => 'Daedalus cycle change triggered successfully'], Response::HTTP_OK);
    }

    private function denyAccessIfNotAdmin(): void
    {
        $admin = $this->getUser();
        if (!$admin instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Request author user not found');
        }
        if (!$admin->isAdmin()) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Only admins can use this endpoint!');
        }
    }
}
