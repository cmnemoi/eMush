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
    public function acceptRulesEndpoint(): View
    {
        $this->denyAccessUnlessGranted(UserVoter::IS_CONNECTED, message: 'You must be connected to accept the rules.');

        $this->userService->acceptRules($this->getUserOrThrow());

        return $this->view(['detail' => 'Rules accepted successfully'], Response::HTTP_OK);
    }

    /**
     * Check if user has not read latest news.
     *
     * @OA\Tag(name="User")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/has-not-read-latest-news")
     */
    public function hasNotReadLatestNewsEndpoint(): View
    {
        $this->denyAccessUnlessGranted(UserVoter::IS_CONNECTED, message: 'You must be connected to read the latest news.');

        return $this->view(['detail' => $this->getUserOrThrow()->hasNotReadLatestNews()], Response::HTTP_OK);
    }

    /**
     * Read latest news.
     *
     * @OA\Tag(name="User")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch(path="/read-latest-news")
     */
    public function readLatestNewsEndpoint(): View
    {
        $this->denyAccessUnlessGranted(UserVoter::IS_CONNECTED, message: 'You must be connected to read the latest news.');

        $this->userService->readLatestNews($this->getUserOrThrow());

        return $this->view(['detail' => 'News read successfully'], Response::HTTP_OK);
    }

    private function getUserOrThrow(): User
    {
        return $this->getUser() instanceof User ? $this->getUser() : throw new \RuntimeException('User not found');
    }
}
