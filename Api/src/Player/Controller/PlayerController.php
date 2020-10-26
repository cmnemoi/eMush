<?php

namespace Mush\Player\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
