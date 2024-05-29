<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\MetaGame\UseCase\ResetRulesAcceptanceForAllUsersUseCase;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class for actions that can be performed by admins.
 *
 * @Route(path="/admin/actions/reset-rules-acceptance")
 */
final class ResetRulesAcceptanceController extends AbstractFOSRestController
{
    public function __construct(
        private ResetRulesAcceptanceForAllUsersUseCase $resetRulesAcceptanceForAllUsersUseCase
    ) {
        $this->resetRulesAcceptanceForAllUsersUseCase = $resetRulesAcceptanceForAllUsersUseCase;
    }

    /**
     * Reset rules acceptance for all users.
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Rest\Put(path="")
     */
    public function resetRulesAcceptanceEndpoint(): View
    {
        $this->resetRulesAcceptanceForAllUsersUseCase->execute();

        return $this->view(['detail' => 'Rules acceptance status successfully reset for all users.'], Response::HTTP_OK);
    }
}
