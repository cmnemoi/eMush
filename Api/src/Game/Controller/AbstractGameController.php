<?php

declare(strict_types=1);

namespace Mush\Game\Controller;

use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractGameController extends AbstractController
{
    protected AdminServiceInterface $adminService;

    public function __construct(AdminServiceInterface $adminService)
    {
        $this->adminService = $adminService;
    }

    protected function denyAccessIfGameInMaintenance(): ?JsonResponse
    {
        if ($this->adminService->isGameInMaintenance()) {
            return $this->json(['detail' => 'gameInMaintenance'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return null;
    }

    /**
     * Checks if the user roles are present for SuperAdmin or Admin.
     *
     * @throws \LogicException if the user doesn't belong to our requirements
     */
    protected function denyUnlessUserAdmin(User $user): void
    {
        if (!$user->isAdmin()) {
            throw $this->createAccessDeniedException('User is not an admin');
        }
    }
}
