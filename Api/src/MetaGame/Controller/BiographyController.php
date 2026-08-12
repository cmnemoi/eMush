<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\MetaGame\Service\GetCharacterBiographyService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Biography')]
final class BiographyController extends AbstractController
{
    public function __construct(private GetCharacterBiographyService $getCharacterBiography) {}

    #[Route('/biography/{characterName}', methods: ['GET'])]
    public function getBiography(Request $request, string $characterName, #[MapQueryParameter] string $language): JsonResponse
    {
        $fullBiography = $this->getCharacterBiography->execute($characterName, $language);

        return $this->withCache(
            $this->json($fullBiography, context: ['language' => $language]),
            $request,
        );
    }

    private function withCache(JsonResponse $response, Request $request): JsonResponse
    {
        $response->setEtag(hash('sha256', (string) $response->getContent()));
        $response->setCache([
            'public' => true,
            'no_cache' => true,
        ]);
        $response->isNotModified($request);

        return $response;
    }
}
