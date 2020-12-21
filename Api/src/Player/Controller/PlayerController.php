<?php

namespace Mush\Player\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Validator\ErrorHandlerTrait;
use Mush\Player\Entity\Dto\PlayerRequest;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Voter\CharacterVoter;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UsersController.
 *
 * @Route(path="/player")
 */
class PlayerController extends AbstractFOSRestController
{
    use ErrorHandlerTrait;

    private PlayerServiceInterface $playerService;
    private DaedalusServiceInterface $daedalusService;
    private CycleServiceInterface $cycleService;
    private ValidatorInterface $validator;

    /**
     * PlayerController constructor.
     */
    public function __construct(
        PlayerServiceInterface $playerService,
        DaedalusServiceInterface $daedalusService,
        CycleServiceInterface $cycleService,
        ValidatorInterface $validator
    ) {
        $this->playerService = $playerService;
        $this->daedalusService = $daedalusService;
        $this->cycleService = $cycleService;
        $this->validator = $validator;
    }

    /**
     * Display Player in-game information.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The player id",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Player")
     * @Security(name="Bearer")
     * @Rest\Get(path="/{id}")
     */
    public function getPlayerAction(Player $player): Response
    {
        $this->denyAccessUnlessGranted(CharacterVoter::PLAYER_VIEW, $player);

        $view = $this->view($player, 200);

        return $this->handleView($view);
    }

    /**
     * Create a player.
     *
     * @OA\RequestBody (
     *      description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *      @OA\Schema(
     *              type="object",
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
     * @OA\Tag(name="Player")
     * @Security(name="Bearer")
     * @ParamConverter("playerRequest", converter="PlayerRequestConverter")
     * @Rest\Post(path="")
     * @Rest\View()
     */
    public function createPlayerAction(PlayerRequest $playerRequest): View
    {
        if (count($violations = $this->validator->validate($playerRequest))) {
            return $this->view($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->denyAccessUnlessGranted(CharacterVoter::PLAYER_CREATE);

        $player = $this->playerService->createPlayer($playerRequest->getDaedalus(), $playerRequest->getCharacter());

        return $this->view($player, Response::HTTP_CREATED);
    }
}
