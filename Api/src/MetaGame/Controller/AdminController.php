<?php

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Validator\ErrorHandlerTrait;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Entity\User;
use Mush\User\Service\UserServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController.
 *
 * @Route(path="/admin")
 */
class AdminController extends AbstractFOSRestController
{
    use ErrorHandlerTrait;

    private DaedalusServiceInterface $daedalusService;
    private PlaceServiceInterface $placeService;
    private PlayerServiceInterface $playerService;
    private UserServiceInterface $userService;

    public function __construct(DaedalusServiceInterface $daedalusService,
        PlaceServiceInterface $placeService,
        PlayerServiceInterface $playerService,
        UserServiceInterface $userService
    ) {
        $this->daedalusService = $daedalusService;
        $this->placeService = $placeService;
        $this->playerService = $playerService;
        $this->userService = $userService;
    }

    /**
     * Close (archive) a player so their user can join another game.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The player to close id",
     *
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/close-player/{id}")
     *
     * @Rest\View()
     */
    public function closePlayer(Request $request): View
    {
        $admin = $this->getUser();
        if (!$admin instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Request author user not found');
        }
        if (!$admin->isAdmin()) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Only admins can close players this way');
        }

        $playerId = intval($request->get('id'));
        $playerToClose = $this->playerService->findById($playerId);
        if (!$playerToClose instanceof Player) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Player to close not found');
        }
        if ($playerToClose->isAlive()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Player to close is still alive');
        }

        if ($this->playerService->endPlayer($playerToClose, '')) {
            return $this->view('Player closed successfully', Response::HTTP_OK);
        }
    }

    /**
     * Add newly added rooms to a Daedalus after an update.
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The daedalus id",
     *
     *       @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/add-new-rooms-to-daedalus/{id}")
     *
     * @Rest\View()
     */
    public function addNewRoomsToDaedalus(Request $request): View
    {
        $admin = $this->getUser();
        if (!$admin instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Request author user not found');
        }
        if (!$admin->isAdmin()) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Only admins can add rooms to Daedalus');
        }

        $daedalusId = intval($request->get('id'));
        $daedalus = $this->daedalusService->findById($daedalusId);
        if (!$daedalus) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Daedalus not found');
        }
        if ($daedalus->getDaedalusInfo()->isDaedalusFinished()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Won't add rooms to a finished Daedalus");
        }

        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $daedalus->getGameConfig()->getDaedalusConfig();

        /** @var PlaceConfig $placeConfig */
        foreach ($daedalusConfig->getPlaceConfigs() as $placeConfig) {
            // don't add rooms that already exist
            if ($daedalus->getPlaceByName($placeConfig->getPlaceName()) instanceof Place) {
                continue;
            }

            $place = $this->placeService->createPlace($placeConfig, $daedalus, ['admin'], new \DateTime());
            $daedalus->addPlace($place);
        }

        return $this->view('Rooms added successfully', Response::HTTP_OK);
    }
}
