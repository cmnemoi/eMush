<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Entity\Collection\AlertElementCollection;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Chat\Services\NeronMessageServiceInterface;
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
use Mush\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class AdminController extends AbstractController
{
    use ErrorHandlerTrait;

    private AdminServiceInterface $adminService;
    private AlertServiceInterface $alertService;
    private DaedalusServiceInterface $daedalusService;
    private ExplorationServiceInterface $explorationService;
    private PlaceServiceInterface $placeService;
    private PlayerServiceInterface $playerService;
    private NeronMessageServiceInterface $neronMessageService;

    public function __construct(
        AdminServiceInterface $adminService,
        AlertServiceInterface $alertService,
        DaedalusServiceInterface $daedalusService,
        ExplorationServiceInterface $explorationService,
        PlaceServiceInterface $placeService,
        PlayerServiceInterface $playerService,
        NeronMessageServiceInterface $neronMessageService,
    ) {
        $this->adminService = $adminService;
        $this->alertService = $alertService;
        $this->daedalusService = $daedalusService;
        $this->explorationService = $explorationService;
        $this->placeService = $placeService;
        $this->playerService = $playerService;
        $this->neronMessageService = $neronMessageService;
    }

    /**
     * Add newly added rooms to a Daedalus after an update.
     */
    #[Route('/add-new-rooms-to-daedalus/{id}', methods: ['POST'])]
    public function addNewRoomsToDaedalus(Request $request): JsonResponse
    {
        $this->denyAccessIfNotAdmin();

        $daedalusId = (int) $request->query->get('id');
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

        return $this->json('Rooms added successfully', Response::HTTP_OK);
    }

    /**
     * Close (archive) a player so their user can join another game.
     */
    #[Route('/close-player/{id}', methods: ['POST'])]
    public function closePlayer(Request $request): JsonResponse
    {
        $this->denyAccessIfNotAdmin();

        $playerId = (int) $request->query->get('id');
        $playerToClose = $this->playerService->findById($playerId);
        if (!$playerToClose instanceof Player) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Player to close not found');
        }
        if ($playerToClose->isAlive()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Player to close is still alive');
        }

        $this->playerService->endPlayer($playerToClose, '', []);

        return $this->json('Player closed successfully', Response::HTTP_OK);
    }

    /**
     * Delete Daedalus duplicated alert elements.
     */
    #[Route('/delete-daedalus-duplicated-alert-elements/{id}', methods: ['POST'])]
    public function deleteDaedalusDuplicatedAlertElements(Request $request): JsonResponse
    {
        $this->denyAccessIfNotAdmin();

        $daedalusId = (int) $request->query->get('id');
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

        return $this->json("{$numberOfElementsDeleted} alert elements deleted successfully", Response::HTTP_OK);
    }

    /**
     * Close all players after a Super Nova.
     */
    #[Route('/close-all-players', methods: ['POST'])]
    public function closeAllPlayers(): JsonResponse
    {
        $this->denyAccessIfNotAdmin();

        $players = $this->playerService->findAll();

        $nbPlayersToClose = \count($players);
        $nbPlayersClosed = 0;

        foreach ($players as $player) {
            if ($player->isAlive()) {
                return $this->json('Some players are still alive', Response::HTTP_BAD_REQUEST);
            }

            try {
                $this->playerService->endPlayer($player, '', []);
                ++$nbPlayersClosed;
            } catch (\Exception $e) {
                continue;
            }
        }

        return $this->json("{$nbPlayersClosed} / {$nbPlayersToClose} players closed successfully", Response::HTTP_OK);
    }

    /**
     * Get maintenance status.
     */
    #[Route('/maintenance', methods: ['GET'])]
    public function getMaintenanceStatus(): JsonResponse
    {
        $isGameInMaintenance = $this->adminService->isGameInMaintenance();

        return $this->json(['gameInMaintenance' => $isGameInMaintenance], Response::HTTP_OK);
    }

    /**
     * Put the game in maintenance mode.
     */
    #[Route('/maintenance', methods: ['POST'])]
    public function putGameInMaintenance(): JsonResponse
    {
        $this->denyAccessIfNotAdmin();

        if ($this->adminService->isGameInMaintenance()) {
            return $this->json('Game is already in maintenance', Response::HTTP_BAD_REQUEST);
        }

        $this->adminService->putGameInMaintenance();

        return $this->json('Game put in maintenance successfully', Response::HTTP_OK);
    }

    /**
     * Remove the game from maintenance mode.
     */
    #[Route('/maintenance', methods: ['DELETE'])]
    public function removeGameFromMaintenance(): JsonResponse
    {
        $this->denyAccessIfNotAdmin();

        if (!$this->adminService->isGameInMaintenance()) {
            return $this->json('Game is not in maintenance', Response::HTTP_BAD_REQUEST);
        }

        $this->adminService->removeGameFromMaintenance();

        return $this->json('Game removed from maintenance successfully', Response::HTTP_OK);
    }

    /**
     * Send a NERON announcement to all non-finished Daedaluses.
     */
    #[Route('/neron-announcement', methods: ['POST'])]
    public function sendNeronAnnouncementToDaedaluses(Request $request): JsonResponse
    {
        $this->denyAccessIfNotAdmin();

        $requestContent = json_decode($request->getContent(), true);

        if (!\array_key_exists('announcement', $requestContent)) {
            return $this->json('Announcement content is missing', Response::HTTP_BAD_REQUEST);
        }

        if (!\array_key_exists('language', $requestContent)) {
            return $this->json('Annoucement language is missing', Response::HTTP_BAD_REQUEST);
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

        return $this->json([
            'message' => "Announcement sent successfully to {$daedaluses->count()} Daedaluses",
            'announcement' => $announcement,
        ], Response::HTTP_CREATED);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Route('/neron-announcement-targeted', methods: ['POST'])]
    public function sendNeronAnnouncementToASingleDaedalus(Request $request): JsonResponse
    {
        $requestContent = json_decode($request->getContent(), true);

        if (!\array_key_exists('announcement', $requestContent)) {
            return $this->json('Announcement content is missing', Response::HTTP_BAD_REQUEST);
        }

        if (!\array_key_exists('shipId', $requestContent)) {
            return $this->json('Ship Id is missing', Response::HTTP_BAD_REQUEST);
        }

        $shipId = $requestContent['shipId'];
        $daedalus = $this->daedalusService->findById($shipId);

        if ($daedalus === null) {
            return $this->json("Daedalus doesn't exist", Response::HTTP_BAD_REQUEST);
        }

        $announcement = $requestContent['announcement'];

        $this->neronMessageService->createNeronMessage(
            messageKey: $announcement,
            daedalus: $daedalus,
            parameters: [],
            dateTime: new \DateTime()
        );

        return $this->json([
            'message' => 'Announcement sent successfully.',
            'announcement' => $announcement,
        ], Response::HTTP_CREATED);
    }

    /**
     * Closes an exploration.
     */
    #[Route('/close-exploration/{id}', methods: ['PATCH'])]
    public function closeExploration(Exploration $exploration): JsonResponse
    {
        $this->denyAccessIfNotAdmin();

        $this->explorationService->closeExploration($exploration, reasons: []);

        return $this->json('Exploration closed successfully', Response::HTTP_OK);
    }

    private function alertElementHaveSameEquipmentOrPlace(AlertElement $element1, AlertElement $element2): bool
    {
        $eq1 = $element1->getEquipment();
        $eq2 = $element2->getEquipment();
        if ($eq1 !== null && $eq2 !== null) {
            return $eq1->getId() === $eq2->getId();
        }

        $pl1 = $element1->getPlace();
        $pl2 = $element2->getPlace();
        if ($pl1 !== null && $pl2 !== null) {
            return $pl1->getId() === $pl2->getId();
        }

        return false;
    }

    private function getPotentiallyDuplicatedAlertElements(AlertElementCollection $alertElements): AlertElementCollection
    {
        $examined = new AlertElementCollection();

        foreach ($alertElements as $element) {
            if ($element->getEquipment() || $element->getPlace()) {
                $examined->add($element);
            }
        }

        return $examined;
    }

    private function removeElement(AlertElementCollection $collection, $element): AlertElementCollection
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
