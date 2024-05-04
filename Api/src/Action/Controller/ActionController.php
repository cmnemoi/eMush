<?php

namespace Mush\Action\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Action\Entity\ActionResult\Error;
use Mush\Action\Entity\Dto\ActionRequest;
use Mush\Action\Service\ActionStrategyServiceInterface;
use Mush\Game\Controller\AbstractGameController;
use Mush\Game\Service\CycleServiceInterface;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UsersController.
 */
class ActionController extends AbstractGameController
{
    private ActionStrategyServiceInterface $actionStrategyService;
    private CycleServiceInterface $cycleService;

    public function __construct(
        AdminServiceInterface $adminService,
        CycleServiceInterface $cycleService,
        ActionStrategyServiceInterface $actionStrategyService,
    ) {
        parent::__construct($adminService);
        $this->actionStrategyService = $actionStrategyService;
        $this->cycleService = $cycleService;
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
     *                  property="params",
     *                  description="Informations to execute the action",
     *                  type="object",
     *                      @OA\Property(
     *                          property="target",
     *                          description="The target of the action",
     *                          type="object",
     *                          oneOf={
     *
     *                              @OA\Schema(
     *                                  type="object",
     *
     *                                  @OA\Property(
     *                                      property="door",
     *                                      description="The id of the door targeted",
     *                                      type="integer",
     *                                  ),
     *                              ),
     *
     *                              @OA\Schema(
     *                                  type="object",
     *
     *                                  @OA\Property(
     *                                      property="item",
     *                                      description="The id of the item targeted",
     *                                      type="integer",
     *                                  ),
     *                              ),
     *
     *                              @OA\Schema(
     *                                  type="object",
     *
     *                                  @OA\Property(
     *                                      property="player",
     *                                      description="The id of the player targeted",
     *                                      type="integer",
     *                                  ),
     *                              ),
     *
     *                              @OA\Schema(
     *                                  type="object",
     *
     *                                  @OA\Property(
     *                                      property="hunter",
     *                                      description="The id of the hunter targeted",
     *                                      type="integer",
     *                                   ),
     *                               ),
     *                      @OA\Property(
     *                          property="content",
     *                          description="A message writen by the user",
     *                          type="string",
     *                      ),
     *                      @OA\Property(
     *                          property="actionProvider",
     *                          description="The actionProvider",
     *                          type="object",
     *                          oneOf={
     *
     *                              @OA\Schema(
     *                                  type="object",
     *
     *                                  @OA\Property(
     *                                      property="equipment",
     *                                      description="The id of the equipment provider",
     *                                      type="integer",
     *                                  ),
     *                              ),
     *
     *                              @OA\Schema(
     *                                  type="object",
     *
     *                                  @OA\Property(
     *                                      property="status",
     *                                      description="The id of the status provider",
     *                                      type="integer",
     *                                  ),
     *                              ),
     *
     *                              @OA\Schema(
     *                                  type="object",
     *
     *                                  @OA\Property(
     *                                      property="player",
     *                                      description="The id of the player provider",
     *                                      type="integer",
     *                                  ),
     *                              ),
     *
     *                              @OA\Schema(
     *                                  type="object",
     *
     *                                  @OA\Property(
     *                                      property="place",
     *                                      description="The id of the place provider",
     *                                      type="integer",
     *                                  ),
     *                              ),
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
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();

        // @TODO: use Voter
        if ($player->getPlayerInfo()->getUser() !== $user) {
            throw new AccessDeniedException('player must be the same as user');
        }
        $daedalus = $player->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        try {
            $result = $this->actionStrategyService->executeAction(
                $player,
                $actionRequest->getAction(),
                $actionRequest->getParams()
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->view($this->view(['error' => $exception->getMessage()], 422));
        }

        if ($result instanceof Error) {
            $view = $this->view(['error' => $result->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            $view = $this->view([
                'actionResult' => $result->getName(),
                'actionDetails' => $result->getDetails(),
            ]);
        }

        $context = new Context();
        $context->setAttribute('currentPlayer', $player);

        $view->setContext($context);

        return $view;
    }
}
