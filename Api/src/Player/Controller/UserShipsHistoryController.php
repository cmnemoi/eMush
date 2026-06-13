<?php

declare(strict_types=1);

namespace Mush\Player\Controller;

use Mush\Game\Enum\LanguageEnum;
use Mush\Player\Query\UserShipsHistoryQuery;
use Mush\Player\Query\UserShipsHistoryQueryHandler;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Player')]
final class UserShipsHistoryController extends AbstractController
{
    public function __construct(private UserShipsHistoryQueryHandler $queryHandler) {}

    /**
     * Get user ships history.
     */
    #[OA\Parameter(
        name: 'userId',
        in: 'query',
        description: 'User UUID',
        required: true,
        example: 1,
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Page number',
        required: true,
        example: 1,
    )]
    #[OA\Parameter(
        name: 'itemsPerPage',
        in: 'query',
        description: 'Number of items per page',
        required: true,
        example: 6,
    )]
    #[OA\Parameter(
        name: 'language',
        in: 'query',
        description: 'Language',
        required: true,
        example: LanguageEnum::ENGLISH,
    )]
    #[Route('/players/ships-history', methods: ['GET'])]
    public function __invoke(#[MapQueryString] UserShipsHistoryQuery $userShipsHistoryQuery): JsonResponse
    {
        $results = $this->queryHandler->execute($userShipsHistoryQuery);

        return $this->json($results, context: ['language' => $userShipsHistoryQuery->language]);
    }
}
