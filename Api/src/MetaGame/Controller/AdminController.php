<?php

namespace Mush\MetaGame\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Validator\ErrorHandlerTrait;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
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

    private AdminServiceInterface $adminService;
    private AlertServiceInterface $alertService;
    private DaedalusServiceInterface $daedalusService;
    private ExplorationServiceInterface $explorationService;
    private PlaceServiceInterface $placeService;
    private PlayerServiceInterface $playerService;
    private UserServiceInterface $userService;
    private NeronMessageServiceInterface $neronMessageService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        AdminServiceInterface $adminService,
        AlertServiceInterface $alertService,
        DaedalusServiceInterface $daedalusService,
        ExplorationServiceInterface $explorationService,
        PlaceServiceInterface $placeService,
        PlayerServiceInterface $playerService,
        UserServiceInterface $userService,
        NeronMessageServiceInterface $neronMessageService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->adminService = $adminService;
        $this->alertService = $alertService;
        $this->daedalusService = $daedalusService;
        $this->explorationService = $explorationService;
        $this->placeService = $placeService;
        $this->playerService = $playerService;
        $this->userService = $userService;
        $this->neronMessageService = $neronMessageService;
        $this->roomLogService = $roomLogService;
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
        $this->denyAccessIfNotAdmin();

        $daedalusId = intval($request->get('id'));
        $daedalus = $this->daedalusService->findById($daedalusId);
        if (!$daedalus) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Daedalus not found');
        }
        if ($daedalus->getDaedalusInfo()->isDaedalusFinished()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Won't add rooms to a finished Daedalus");
        }

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
        $this->denyAccessIfNotAdmin();

        $playerId = intval($request->get('id'));
        $playerToClose = $this->playerService->findById($playerId);
        if (!$playerToClose instanceof Player) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Player to close not found');
        }
        if ($playerToClose->isAlive()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Player to close is still alive');
        }

        if ($this->playerService->endPlayer($playerToClose, '', [])) {
            return $this->view('Player closed successfully', Response::HTTP_OK);
        }

        throw new \Exception('impossible to close player');
    }

    /**
     * Delete Daedalus duplicated alert elements.
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
     * @Rest\Post(path="/delete-daedalus-duplicated-alert-elements/{id}")
     *
     * @Rest\View()
     */
    public function deleteDaedalusDuplicatedAlertElements(Request $request): View
    {
        $this->denyAccessIfNotAdmin();

        $daedalusId = intval($request->get('id'));
        $daedalus = $this->daedalusService->findById($daedalusId);
        if (!$daedalus) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Daedalus not found');
        }
        if ($daedalus->getDaedalusInfo()->isDaedalusFinished()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Won't delete alert elements of a finished Daedalus");
        }

        $daedalusAlerts = $this->alertService->findByDaedalus($daedalus);

        $numberOfElementsDeleted = 0;

        /** @var Alert $alert */
        foreach ($daedalusAlerts as $alert) {
            $alertElements = $alert->getAlertElements();

            if ($alertElements->count() < 2) {
                continue;
            }

            $potentiallyDuplicatedAlertElements = $this->getPotentiallyDuplicatedAlertElements($alertElements);

            /** @var AlertElement $alertElementToExamine */
            foreach ($potentiallyDuplicatedAlertElements as $alertElementToExamine) {
                $remainingElements = $this->removeElement($potentiallyDuplicatedAlertElements, $alertElementToExamine);

                /** @var AlertElement $alertElement */
                foreach ($remainingElements as $alertElement) {
                    if ($this->alertElementHaveSameEquipmentOrPlace($alertElementToExamine, $alertElement)) {
                        $alert->getAlertElements()->removeElement($alertElement);
                        $this->alertService->deleteAlertElement($alertElement);
                        ++$numberOfElementsDeleted;
                    }
                }
            }
        }

        return $this->view("{$numberOfElementsDeleted} alert elements deleted successfully", Response::HTTP_OK);
    }

    /**
     * Close all players after a Super Nova.
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/close-all-players")
     */
    public function closeAllPlayers(): View
    {
        $this->denyAccessIfNotAdmin();

        $players = $this->playerService->findAll();
        foreach ($players as $player) {
            if ($player->isAlive()) {
                return $this->view('Some players are still alive', Response::HTTP_BAD_REQUEST);
            }
            $this->playerService->endPlayer($player, '', []);
        }

        return $this->view('All players closed successfully', Response::HTTP_OK);
    }

    /**
     * Get maintenance status.
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/maintenance")
     */
    public function getMaintenanceStatus(): View
    {
        $isGameInMaintenance = $this->adminService->isGameInMaintenance();

        return $this->view(['gameInMaintenance' => $isGameInMaintenance], Response::HTTP_OK);
    }

    /**
     * Put the game in maintenance mode.
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/maintenance")
     */
    public function putGameInMaintenance(): View
    {
        $this->denyAccessIfNotAdmin();

        if ($this->adminService->isGameInMaintenance()) {
            return $this->view('Game is already in maintenance', Response::HTTP_BAD_REQUEST);
        }

        $this->adminService->putGameInMaintenance();

        return $this->view('Game put in maintenance successfully', Response::HTTP_OK);
    }

    /**
     * Remove the game from maintenance mode.
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Delete(path="/maintenance")
     */
    public function removeGameFromMaintenance(): View
    {
        $this->denyAccessIfNotAdmin();

        if (!$this->adminService->isGameInMaintenance()) {
            return $this->view('Game is not in maintenance', Response::HTTP_BAD_REQUEST);
        }

        $this->adminService->removeGameFromMaintenance();

        return $this->view('Game removed from maintenance successfully', Response::HTTP_OK);
    }

    /**
     * Send a NERON announcement to all non-finished Daedaluses.
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
     *                  property="announcement",
     *                  description="The announcement to send",
     *              ),
     *
     *          ),
     *
     *          @OA\Schema(
     *             type="object",
     *
     *            @OA\Property(
     *               type="string",
     *               property="language",
     *               description="The language of the announcement",
     *           ),
     *      )
     *    )
     * )
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/neron-announcement")
     */
    public function sendNeronAnnouncementToDaedaluses(Request $request): View
    {
        $this->denyAccessIfNotAdmin();

        $requestContent = json_decode($request->getContent(), true);

        if (!array_key_exists('announcement', $requestContent)) {
            return $this->view('Announcement content is missing', Response::HTTP_BAD_REQUEST);
        }

        if (!array_key_exists('language', $requestContent)) {
            return $this->view('Annoucement language is missing', Response::HTTP_BAD_REQUEST);
        }

        $announcement = $requestContent['announcement'];
        $language = $requestContent['language'];

        $daedaluses = $this->daedalusService->findAllNonFinishedDaedalusesByLanguage($language);
        foreach ($daedaluses as $daedalus) {
            $this->neronMessageService->createNeronMessage(
                messageKey: $announcement,
                daedalus: $daedalus,
                parameters: [],
                dateTime: new \DateTime()
            );
        }

        return $this->view([
            'message' => "Announcement sent successfully to {$daedaluses->count()} Daedaluses",
            'announcement' => $announcement,
        ], Response::HTTP_CREATED);
    }

    public function closeExploration(Exploration $exploration): View
    {
        $this->denyAccessIfNotAdmin();

        $this->explorationService->closeExploration($exploration, reasons: []);

        return $this->view('Exploration closed successfully', Response::HTTP_OK);
    }

    private function alertElementHaveSameEquipmentOrPlace(AlertElement $element1, AlertElement $element2): bool
    {
        if ($element1->getEquipment() && $element2->getEquipment()) {
            return $element1->getEquipment()->getId() === $element2->getEquipment()->getId();
        }

        if ($element1->getPlace() && $element2->getPlace()) {
            return $element1->getPlace()->getId() === $element2->getPlace()->getId();
        }

        return false;
    }

    private function getPotentiallyDuplicatedAlertElements(Collection $alertElements): Collection
    {
        $examined = new ArrayCollection();

        foreach ($alertElements as $element) {
            if ($element->getEquipment() || $element->getPlace()) {
                $examined->add($element);
            }
        }

        return $examined;
    }

    private function removeElement(Collection $collection, $element): Collection
    {
        $collection->removeElement($element);

        return $collection;
    }

    private function denyAccessIfNotAdmin(): void
    {
        $admin = $this->getUser();
        if (!$admin instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Request author user not found');
        }
        if (!$admin->isAdmin()) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Only admins can use this endpoint!');
        }
    }
}
