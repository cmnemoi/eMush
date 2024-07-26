<?php

namespace Mush\Player\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Game\Controller\AbstractGameController;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Validator\ErrorHandlerTrait;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Dto\ChooseSkillDto;
use Mush\Player\Entity\Dto\PlayerCreateRequest;
use Mush\Player\Entity\Dto\PlayerEndRequest;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Voter\PlayerVoter;
use Mush\Skill\UseCase\AddSkillToPlayerUseCase;
use Mush\User\Entity\User;
use Mush\User\Voter\UserVoter;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(path="/player")
 */
class PlayerController extends AbstractGameController
{
    use ErrorHandlerTrait;

    private PlayerServiceInterface $playerService;
    private CycleServiceInterface $cycleService;
    private ValidatorInterface $validator;
    private AddSkillToPlayerUseCase $addSkillToPlayerUseCase;

    public function __construct(
        AdminServiceInterface $adminService,
        PlayerServiceInterface $playerService,
        CycleServiceInterface $cycleStrategyService,
        ValidatorInterface $validator,
        AddSkillToPlayerUseCase $addSkillToPlayerUseCase
    ) {
        parent::__construct($adminService);
        $this->playerService = $playerService;
        $this->cycleService = $cycleStrategyService;
        $this->validator = $validator;
        $this->addSkillToPlayerUseCase = $addSkillToPlayerUseCase;
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
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

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

        if (\count($violations = $this->validator->validate($playerCreateRequest))) {
            return $this->view($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_CREATE);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        $daedalus = $playerCreateRequest->getDaedalus();
        if (!$daedalus) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'No Daedalus found.');
        }

        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        if ($daedalus->getDaedalusInfo()->isDaedalusFinished()) {
            return $this->view(["Can't create player : Daedalus is already finished"], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $character = $playerCreateRequest->getCharacter();
        if (!$character) {
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
     *                  @OA\Property(
     *                     property="likedPlayers",
     *                     description="The other players that the player likes",
     *                     type="array",
     *
     *                     @OA\Items(type="integer")
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

        if (\count($violations = $this->validator->validate($request))) {
            return $this->view($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_END, $player);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        if ($player->getPlayerInfo()->getGameStatus() !== GameStatusEnum::FINISHED) {
            return $this->view(['message' => 'Player cannot end game'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var string $message */
        $message = $request->getMessage();
        $likedPlayers = $request->getLikedPlayers();
        $this->playerService->endPlayer($player, $message, $likedPlayers);

        return $this->view(null, 200);
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
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        $result = $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $player->getDaedalus());

        if ($result->noCycleElapsed()) {
            return $this->view(['message' => 'No cycle change triggered'], Response::HTTP_OK);
        }
        if ($result->hasDaedalusCycleElapsed()) {
            return $this->view(['message' => 'Daedalus cycle change(s) triggered successfully (' . $result->daedalusCyclesElapsed . ' cycle(s) elapsed)'], Response::HTTP_OK);
        }
        if ($result->hasExplorationCycleElapsed()) {
            return $this->view(['message' => 'Exploration cycle change(s) triggered successfully (' . $result->explorationCyclesElapsed . ' cycle(s) elapsed)'], Response::HTTP_OK);
        }
    }

    /**
     * Trigger exploration cycle change.
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
     * @Rest\Get(path="/{id}/exploration-cycle-change")
     *
     * @Rest\View()
     */
    public function triggerExplorationCycleChange(Player $player): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        if (!$player->isExploring()) {
            return $this->view(['message' => 'You have to be in an exploration to do that!'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($player->getDaedalus()->isCycleChange()) {
            return $this->view(['message' => 'Daedalus is changing cycle'], Response::HTTP_CONFLICT);
        }
        $result = $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $player->getDaedalus());

        if ($result->noCycleElapsed()) {
            return $this->view(['message' => 'No cycle change triggered'], Response::HTTP_OK);
        }

        return $this->view(['message' => 'Exploration cycle change(s) triggered successfully (' . $result->explorationCyclesElapsed . ' cycle(s) elapsed)'], Response::HTTP_OK);
    }

    /**
     * Choose a skill.
     *
     * @OA\RequestBody (
     *      description="Input data format",
     *
     *      @OA\MediaType(
     *          mediaType="application/json",
     *
     *          @OA\Schema(
     *              type="object",
     *
     *              @OA\Property(
     *                  type="string",
     *                  property="skill",
     *                  description="The skill to choose",
     *              ),
     *
     *          ),
     *      )
     *    )
     * )
     *
     * @OA\Tag(name="Player")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/{id}/choose-skill")
     *
     * @Rest\View()
     */
    public function chooseSkillEndpoint(Request $request, Player $player): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        $chooseSkillDto = new ChooseSkillDto($request);

        $this->addSkillToPlayerUseCase->execute(skill: $chooseSkillDto->skill, player: $player);

        return $this->view(['detail' => 'Skill selected successfully'], Response::HTTP_CREATED);
    }
}
