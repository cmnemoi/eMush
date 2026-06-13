<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\MetaGame\Service\GetCharacterBiographyService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Biography')]
final class BiographyController extends AbstractController
{
    public function __construct(private GetCharacterBiographyService $getCharacterBiography) {}

    #[Route('/biography/{characterName}', methods: ['GET'])]
    public function getBiography(string $characterName, #[MapQueryParameter] string $language): JsonResponse
    {
        $fullBiography = $this->getCharacterBiography->execute($characterName, $language);

        return $this->json($fullBiography, context: ['language' => $language]);
    }
}
