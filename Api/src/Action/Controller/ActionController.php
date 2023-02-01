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
use Psr\Log\LoggerInterface;

/**
 * Class UsersController.
 */
class ActionController extends AbstractFOSRestController
{
    private ActionStrategyServiceInterface $actionService;
    private LoggerInterface $logger;

    public function __construct(
        ActionStrategyServiceInterface $actionService,
        LoggerInterface $logger
    ) {
        $this->actionService = $actionService;
        $this->logger = $logger;
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
     * @OA\Tag(name="Player")
     * @Security(name="Bearer")
     * @ParamConverter("actionRequest", converter="fos_rest.request_body")
     * @Rest\Post(path="/player/{id}/action")
     */
    public function executeActionAction(Player $player, ActionRequest $actionRequest): View
    {
        /** @var User $user */
        $user = $this->getUser();

        // @TODO: use Voter
        if ($player->getPlayerInfo()->getUser() !== $user) {
            $this->logger->error('player user must be the same as request user', [
                'player' => $player->getPlayerInfo()->getUser()->getId(),
                'user' => $user->getId(),
            ]);
            throw new AccessDeniedException('player user must be the same as request user');
        }

        try {
            $result = $this->actionService->executeAction(
                $player,
                $actionRequest->getAction(),
                $actionRequest->getParams()
            );
        } catch (\InvalidArgumentException $exception) {
            $this->logger->error('error executing action', [
                'player' => $player->getId(),
                'action' => $actionRequest->getAction(),
                'params' => $actionRequest->getParams(),
                'error' => $exception->getMessage(),
            ]);
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
