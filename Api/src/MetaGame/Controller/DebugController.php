<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\CycleServiceInterface;
use Mush\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/debug')]
final class DebugController extends AbstractController
{
    private CycleServiceInterface $cycleService;

    public function __construct(CycleServiceInterface $cycleService)
    {
        $this->cycleService = $cycleService;
    }

    /**
     * Force cycle change for a locked-up Daedalus.
     */
    #[Route('/unlock-daedalus/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function forceLockedDaedalusCycleChange(Daedalus $daedalus): JsonResponse
    {
        $this->denyAccessIfNotAdmin();

        if (!$daedalus->isDaedalusOrExplorationChangingCycle()) {
            return $this->json(['error' => 'Daedalus is not on cycle change'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        return $this->json(['detail' => 'Daedalus cycle change triggered successfully'], Response::HTTP_OK);
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
