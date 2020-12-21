<?php

namespace Mush\Action\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Mush\Action\ActionResult\Error;
use Mush\Action\Service\ActionServiceInterface;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UsersController.
 *
 * @Route(path="/action")
 */
class ActionController extends AbstractFOSRestController
{
    private ActionServiceInterface $actionService;

    public function __construct(ActionServiceInterface $actionService)
    {
        $this->actionService = $actionService;
    }

    /**
     * Perform an action.
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
        /** @var User $user */
        $user = $this->getUser();

        if (!($currentPlayer = $user->getCurrentGame())) {
            throw new AccessDeniedException('User must be in game for actions');
        }

        try {
            $result = $this->actionService->executeAction(
                $currentPlayer,
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
