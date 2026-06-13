<?php

declare(strict_types=1);

namespace Mush\User\Controller;

use Mush\User\Entity\User;
use Mush\User\Query\SearchUsersByUsernameQuery;
use Mush\User\Query\SearchUsersByUsernameQueryHandler;
use Mush\User\Service\UserServiceInterface;
use Mush\User\Voter\UserVoter;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class UserController.
 */
#[Route(path: '/users')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly SearchUsersByUsernameQueryHandler $queryHandler,
    ) {}

    /**
     * Get current user information.
     */
    #[OA\Tag(name: 'User')]
    #[Route('/me', methods: ['GET'])]
    public function getCurrentUserEndpoint(): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::IS_CONNECTED, message: 'You must be connected to get user info.');

        return $this->json($this->getUserOrThrow(), Response::HTTP_OK);
    }

    /**
     * Search users by username.
     */
    #[OA\Tag(name: 'User')]
    #[OA\Parameter(name: 'username', in: 'query', required: true, description: 'Username to search for')]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Maximum number of results')]
    #[IsGranted(UserVoter::IS_CONNECTED, message: 'You must be connected to search users.')]
    #[Route('/search', methods: ['GET'])]
    public function searchUsersEndpoint(#[MapQueryString] SearchUsersByUsernameQuery $searchUsersByUsername): JsonResponse
    {
        $results = $this->queryHandler->execute($searchUsersByUsername);

        return $this->json($results, Response::HTTP_OK);
    }

    /**
     * Accept game rules.
     */
    #[OA\Tag(name: 'User')]
    #[Route('/accept-rules', methods: ['PATCH'])]
    public function acceptRulesEndpoint(): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::IS_CONNECTED, message: 'You must be connected to accept the rules.');

        $this->userService->acceptRules($this->getUserOrThrow());

        return $this->json(['detail' => 'Rules accepted successfully'], Response::HTTP_OK);
    }

    /**
     * Check if user has not read latest news.
     */
    #[OA\Tag(name: 'User')]
    #[Route('/has-not-read-latest-news', methods: ['GET'])]
    public function hasNotReadLatestNewsEndpoint(): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::IS_CONNECTED, message: 'You must be connected to read the latest news.');

        return $this->json(['detail' => $this->getUserOrThrow()->hasNotReadLatestNews()], Response::HTTP_OK);
    }

    /**
     * Read latest news.
     */
    #[OA\Tag(name: 'User')]
    #[Route('/read-latest-news', methods: ['PATCH'])]
    public function readLatestNewsEndpoint(): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::IS_CONNECTED, message: 'You must be connected to read the latest news.');

        $this->userService->readLatestNews($this->getUserOrThrow());

        return $this->json(['detail' => 'News read successfully'], Response::HTTP_OK);
    }

    private function getUserOrThrow(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \RuntimeException('User not found');
        }

        return $user;
    }
}
