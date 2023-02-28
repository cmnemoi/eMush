<?php

namespace Mush\Action\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Action\ActionResult\Error;
use Mush\Action\Entity\Dto\ActionRequest;
use Mush\Action\Service\ActionStrategyServiceInterface;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UsersController.
 */
class ActionController extends AbstractFOSRestController
{
    private ActionStrategyServiceInterface $actionService;

    public function __construct(
        ActionStrategyServiceInterface $actionService,
    ) {
        $this->actionService = $actionService;
    }

    /**
     * Perform an action.
     *
     * @OA\RequestBody (
     *      description="Input data format",
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *      @OA\Schema(
     *              type="object",
     *
     *                 @OA\Property(
     *                     property="action",
     *                     description="The action id to perform",
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
     *
     * @OA\Tag(name="Player")
     *
     * @Security(name="Bearer")
     *
     * @ParamConverter("actionRequest", converter="fos_rest.request_body")
     *
     * @Rest\Post(path="/player/{id}/action")
     */
    public function executeActionAction(Player $player, ActionRequest $actionRequest): View
    {
        /** @var User $user */
        $user = $this->getUser();

        // @TODO: use Voter
        if ($player->getPlayerInfo()->getUser() !== $user) {
            throw new AccessDeniedException('player must be the same as user');
        }

        try {
            $result = $this->actionService->executeAction(
                $player,
                $actionRequest->getAction(),
                $actionRequest->getParams()
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->view($this->view(['error' => $exception->getMessage()], 422));
        }

        if ($result instanceof Error) {
            $view = View::create($result->getMessage(), 422);
        } else {
            $view = View::create('Success', 200);
        }

        $context = new Context();
        $context->setAttribute('currentPlayer', $player);

        $view->setContext($context);

        return $view;
    }
}
