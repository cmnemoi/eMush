<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use Mush\MetaGame\Service\GetCharacterBiographyService;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

#[OA\Tag(name: 'Biography')]
#[Security(name: 'Bearer')]
final class BiographyController extends AbstractController
{
    public function __construct(private GetCharacterBiographyService $getCharacterBiography) {}

    #[Get(path: '/biography/{characterName}')]
    public function getBiography(string $characterName, #[MapQueryParameter] string $language): JsonResponse
    {
        $fullBiography = $this->getCharacterBiography->execute($characterName, $language);

        return $this->json($fullBiography, context: ['language' => $language]);
    }
}
