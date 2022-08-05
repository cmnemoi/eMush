<?php

namespace Mush\Game\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigService;
use Mush\Game\Validator\GameConfigInputConstraint;
use Mush\User\Enum\RoleEnum;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'GameConfig')]
#[Route('/game-config')]
class GameConfigController extends AbstractFOSRestController
{
    #[Route('/{id}', name: 'detailGameConfig', methods: ['Get'])]
    #[Security(name: 'Bearer')]
    public function detailGameConfigAction(?GameConfig $gameConfig): View
    {
        if ($gameConfig === null) {
            throw new NotFoundHttpException();
        }

        return $this->view($gameConfig, 200);
    }

    #[Route('/{id}', name: 'updateGameConfig', methods: ['PUT'])]
    #[IsGranted(RoleEnum::ADMIN)]
    #[Security(name: 'Bearer')]
    public function updateGameConfigAction(
        ?GameConfig $gameConfig,
        Request $request,
        SerializerInterface $serializer,
        GameConfigService $gameConfigService,
        ValidatorInterface $validator
    ): View {
        $data = $request->getContent();

        $constraint = new GameConfigInputConstraint();

        $decodedData = $data ? json_decode($data, true) : [];
        $errors = $validator->validate($decodedData, $constraint);
        if ($errors->count() > 0) {
            return new view($errors, 422);
        }

        if ($gameConfig === null) {
            throw new NotFoundHttpException();
        }

        $serializer->deserialize($data, GameConfig::class, 'json', ['object_to_populate' => $gameConfig]);

        $gameConfigService->persist($gameConfig);

        return $this->view($gameConfig, 200);
    }
}
