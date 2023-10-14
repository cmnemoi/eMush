<?php

namespace Mush\Player\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Controller\AbstractGameController;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Validator\ErrorHandlerTrait;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\Dto\PlayerCreateRequest;
use Mush\Player\Entity\Dto\PlayerEndRequest;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Voter\PlayerVoter;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UsersController.
 *
 * @Route(path="/player")
 */
class PlayerController extends AbstractGameController
{
    use ErrorHandlerTrait;

    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;
    private DaedalusServiceInterface $daedalusService;
    private CycleServiceInterface $cycleService;
    private ValidatorInterface $validator;

    private LoggerInterface $logger;

    public function __construct(
        AdminServiceInterface $adminService,
        EventServiceInterface $eventService,
        PlayerServiceInterface $playerService,
        DaedalusServiceInterface $daedalusService,
        CycleServiceInterface $cycleService,
        ValidatorInterface $validator,
        LoggerInterface $loggerInterface
    ) {
        parent::__construct($adminService);
        $this->eventService = $eventService;
        $this->playerService = $playerService;
        $this->daedalusService = $daedalusService;
        $this->cycleService = $cycleService;
        $this->validator = $validator;
        $this->logger = $loggerInterface;
    }

    /**
     * Display Player in-game information.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The player id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Tag(name="Player")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/{id}")
     */
    public function getPlayerAction(Player $player): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);

        $context = new Context();
        $context->setAttribute('currentPlayer', $player);

        $view = $this->view($player, Response::HTTP_OK);
        $view->setContext($context);

        return $view;
    }

    /**
     * Create a player.
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
     *                  @OA\Property(
     *                     property="user",
     *                     description="The user making the request",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="daedalus",
     *                     description="The daedalus to add the player",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="character",
     *                     description="The character selected",
     *                     type="string"
     *                 )
     *             )
     *             )
     *         )
     *     )
     *
     * @OA\Tag(name="Player")
     *
     * @Security(name="Bearer")
     *
     * @ParamConverter("playerCreateRequest", converter="PlayerCreateRequestConverter")
     *
     * @Rest\Post(path="")
     *
     * @Rest\View()
     */
    public function createPlayerAction(PlayerCreateRequest $playerCreateRequest): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        if (count($violations = $this->validator->validate($playerCreateRequest))) {
            return $this->view($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_CREATE);

        $daedalus = $playerCreateRequest->getDaedalus();
        $this->cycleService->handleCycleChange(new \DateTime(), $daedalus);

        if ($daedalus->getDaedalusInfo()->isDaedalusFinished()) {
            return $this->view(["Can't create player : Daedalus is already finished"], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $character = $playerCreateRequest->getCharacter();

        if (!$daedalus || !$character) {
            return $this->view(['invalid parameters'], 422);
        }

        /** @var User $user */
        $user = $this->getUser();

        $player = $this->playerService->createPlayer($daedalus, $user, $character);

        $context = new Context();
        $context->setAttribute('currentPlayer', $player);

        $view = $this->view($player, Response::HTTP_CREATED);
        $view->setContext($context);

        return $view;
    }

    /**
     * End the game for a player.
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
     *                     property="message",
     *                     description="The player last words",
     *                     type="string",
     *                 ),
     *             )
     *             )
     *         )
     *     )
     *
     * @OA\Tag(name="Player")
     *
     * @Security(name="Bearer")
     *
     * @ParamConverter("request", converter="fos_rest.request_body")
     *
     * @Rest\Post(path="/{player}/end")
     *
     * @Rest\View()
     */
    public function endPlayerAction(PlayerEndRequest $request, Player $player): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        if (count($violations = $this->validator->validate($request))) {
            return $this->view($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_END, $player);

        if ($player->getPlayerInfo()->getGameStatus() !== GameStatusEnum::FINISHED) {
            return $this->view(['message' => 'Player cannot end game'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var string $message */
        $message = $request->getMessage();
        $this->playerService->endPlayer($player, $message);

        return $this->view(null, 200);
    }

    /**
     * Quarantine a player.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The player id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Tag(name="Player")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/quarantine/{id}")
     *
     * @Rest\View()
     */
    public function quarantinePlayer(Player $player): View
    {
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_QUARANTINE, $player);

        $deathEvent = new PlayerEvent($player, [EndCauseEnum::QUARANTINE], new \DateTime());
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

        return $this->view(['message' => 'Player quarantined successfully'], Response::HTTP_OK);
    }

    /**
     * Trigger cycle change.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The player id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Tag(name="Player")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/{id}/cycle-change")
     *
     * @Rest\View()
     */
    public function triggerCycleChange(Player $player): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);

        $this->cycleService->handleCycleChange(new \DateTime(), $player->getDaedalus());

        return $this->view(['message' => 'Cycle change triggered successfully'], Response::HTTP_OK);
    }
}
