<?php

namespace Mush\Action\Controller;

use Mush\Action\ActionResult\Error;
use Mush\Action\Service\ActionServiceInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * Class UsersController
 * @package Mush\Controller
 * @Route(path="/action")
 */
class ActionController extends AbstractFOSRestController
{
    private ActionServiceInterface $actionService;

    /**
     * ActionController constructor.
     * @param ActionServiceInterface $actionService
     */
    public function __construct(ActionServiceInterface $actionService)
    {
        $this->actionService = $actionService;
    }

    /**
     * Perform an action
     *
     * @OA\RequestBody (
     *      description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *      @OA\Schema(
     *              type="object",
     *                 @OA\Property(
     *                     property="action",
     *                     description="The action name to perform",
     *                     type="integer",
     *                 ),
     *                  @OA\Property(
     *                  property="parameters",
     *                  type="object",
     *                      @OA\Property(
     *                          property="item",
     *                          description="The item parameter",
     *                          type="integer",
     *                      ),
     *                      @OA\Property(
     *                          property="door",
     *                          description="The door parameter",
     *                          type="integer",
     *                      ),
      *                      @OA\Property(
     *                          property="player",
     *                          description="The player parameter",
     *                          type="integer",
     *                      ),
     *                 )
     *             )
     *         )
     *     )
     * @OA\Tag(name="Action")
     * @Security(name="Bearer")
     * @Rest\Post(path="")
     */
    public function createAction(Request $request): Response
    {
        try {
            $result = $this->actionService->executeAction(
                $this->getUser()->getCurrentGame(),
                $request->get('action'),
                $request->get('params')
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->handleView($this->view(['error' => $exception->getMessage()], 422));
        }

        if ($result instanceof Error) {
            $view = $this->view($result->getMessage(), 422);
        } else {
            $view = $this->view(null, 200);
        }

        return $this->handleView($view);
    }
}
