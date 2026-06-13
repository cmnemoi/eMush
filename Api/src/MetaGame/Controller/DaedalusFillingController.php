<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\MetaGame\Query\GetFillingDaedalusesQuery;
use Mush\MetaGame\Query\GetFillingDaedalusesQueryHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DaedalusFillingController extends AbstractController
{
    public function __construct(private GetFillingDaedalusesQueryHandler $queryHandler) {}

    #[Route('/filling-daedaluses', methods: ['GET'])]
    public function getFillingDaedalusesEndpoint(): JsonResponse
    {
        $results = $this->queryHandler->execute(new GetFillingDaedalusesQuery());

        return $this->json($results);
    }
}
