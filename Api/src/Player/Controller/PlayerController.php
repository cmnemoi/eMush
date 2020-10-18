<?php

namespace Mush\Player\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Mush\Daedalus\Service\DaedalusServiceInterface;
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

    /**
     * PlayerController constructor.
     * @param PlayerServiceInterface $playerService
     * @param DaedalusServiceInterface $daedalusService
     */
    public function __construct(PlayerServiceInterface $playerService, DaedalusServiceInterface $daedalusService)
    {
        $this->playerService = $playerService;
        $this->daedalusService = $daedalusService;
    }

    /**
     * @Rest\Get(path="/{id}")
     */
    public function getPlayerAction(Request $request): Response
    {
        $player = $this->playerService->findById($request->get('id'));

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

        $usser = $this->getUser();

        if (!$daedalus) {
            $this->handleView($this->view('Missing daedalus', 422));
        }

        $player = $this->playerService->createPlayer($daedalus, $character);

        $view = $this->view($player, 201);

        return $this->handleView($view);
    }
}