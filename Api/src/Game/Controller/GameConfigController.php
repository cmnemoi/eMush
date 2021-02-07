<?php


namespace Mush\Game\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use OpenApi\Annotations as OA;
use Mush\Game\Entity\GameConfig;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class GameConfigController
 * @Route(path="/game-config")
 */
class GameConfigController extends AbstractFOSRestController
{
    /**
     * Display game config informations.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The game-config id",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="GameConfig")
     * @Security(name="Bearer")
     * @Rest\Get(path="/{id}", requirements={"id"="\d+"})
     */
    public function getGameConfigAction(?GameConfig $gameconfig): View
    {
        if ($gameconfig === null) {
            throw new NotFoundHttpException();
        }

        return $this->view($gameconfig, 200);
    }

}