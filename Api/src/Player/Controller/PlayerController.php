<?php

namespace Mush\Player\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * Class UsersController
 * @package Mush\Controller
 * @Route(path="/player")
 */
class PlayerController extends AbstractFOSRestController
{
    private PlayerServiceInterface $playerService;
    private DaedalusServiceInterface $daedalusService;
    private CycleServiceInterface $cycleService;

    /**
     * PlayerController constructor.
     * @param PlayerServiceInterface $playerService
     * @param DaedalusServiceInterface $daedalusService
     * @param CycleServiceInterface $cycleService
     */
    public function __construct(
        PlayerServiceInterface $playerService,
        DaedalusServiceInterface $daedalusService,
        CycleServiceInterface $cycleService
    ) {
        $this->playerService = $playerService;
        $this->daedalusService = $daedalusService;
        $this->cycleService = $cycleService;
    }

    /**
     * Display Player in-game information
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
    public function getPlayerAction(Request $request): Response
    {
        $player = $this->playerService->findById($request->get('id'));

        if (!$player) {
            return $this->handleView($this->view('Not found', 404));
        }

        $this->cycleService->handleCycleChange($player->getDaedalus());

        $view = $this->view($player, 200);

        return $this->handleView($view);
    }

    /**
     * Create a player
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
     * @Rest\Post(path="")
     */
    public function createPlayerAction(Request $request): Response
    {
        $daedalus = $this->daedalusService->findById($request->get('daedalus'));
        $character = $request->get('character');

        if (!$daedalus) {
            $this->handleView($this->view('Missing daedalus', 422));
        }

        $player = $this->playerService->createPlayer($daedalus, $character);

        $view = $this->view($player, 201);

        return $this->handleView($view);
    }
}
