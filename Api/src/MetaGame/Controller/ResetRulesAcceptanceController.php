<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\MetaGame\UseCase\ResetRulesAcceptanceForAllUsersUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/actions/reset-rules-acceptance')]
final class ResetRulesAcceptanceController extends AbstractController
{
    public function __construct(
        private ResetRulesAcceptanceForAllUsersUseCase $resetRulesAcceptanceForAllUsersUseCase
    ) {
        $this->resetRulesAcceptanceForAllUsersUseCase = $resetRulesAcceptanceForAllUsersUseCase;
    }

    /**
     * Reset rules acceptance for all users.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('', methods: ['PUT'])]
    public function resetRulesAcceptanceEndpoint(): JsonResponse
    {
        $this->resetRulesAcceptanceForAllUsersUseCase->execute();

        return $this->json(['detail' => 'Rules acceptance status successfully reset for all users.'], Response::HTTP_OK);
    }
}
