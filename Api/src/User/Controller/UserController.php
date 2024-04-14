<?php

declare(strict_types=1);

namespace Mush\User\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\User\Entity\User;
use Mush\User\Service\UserServiceInterface;
use Mush\User\Voter\UserVoter;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController.
 *
 * @Route(path="/users")
 */
final class UserController extends AbstractFOSRestController
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Accept game rules.
     *
     * @OA\Tag(name="User")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch(path="/accept-rules")
     */
    public function acceptRulesAction(): View
    {
        $this->denyAccessUnlessGranted(UserVoter::IS_CONNECTED, message: 'You must be connected to accept the rules.');

        /** @var User $user */
        $user = $this->getUser();
        $this->userService->acceptRules($user);

        return $this->view(['detail' => 'Rules accepted successfully'], Response::HTTP_OK);
    }
}
