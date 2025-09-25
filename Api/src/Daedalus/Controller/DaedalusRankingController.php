<?php

declare(strict_types=1);

namespace Mush\Daedalus\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use Mush\Daedalus\Query\GetDaedalusRankingQuery;
use Mush\Daedalus\Query\GetDaedalusRankingQueryHandler;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

#[OA\Tag(name: 'Daedalus')]
#[Security(name: 'Bearer')]
final class DaedalusRankingController extends AbstractController
{
    public function __construct(
        private GetDaedalusRankingQueryHandler $handler,
    ) {}

    /**
     * Get daedalus ranking.
     */
    #[OA\Parameter(
        name: 'language',
        in: 'query',
        description: 'Language of the results',
        required: false,
        example: '',
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Page number',
        required: false,
        example: 1,
    )]
    #[OA\Parameter(
        name: 'itemsPerPage',
        in: 'query',
        description: 'Number of items per page',
        required: false,
        example: 10,
    )]
    #[Get(path: '/daedaluses/ranking')]
    public function __invoke(#[MapQueryString] GetDaedalusRankingQuery $daedalusRankingQuery): JsonResponse
    {
        $results = $this->handler->execute($daedalusRankingQuery);

        return $this->json($results, context: ['language' => $daedalusRankingQuery->language]);
    }
}
