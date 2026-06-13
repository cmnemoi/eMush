<?php

declare(strict_types=1);

namespace Mush\Player\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Controller\AbstractGameController;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Validator\ErrorHandlerTrait;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Player\Entity\Dto\PlayerCreateRequest;
use Mush\Player\Entity\Dto\PlayerEndRequest;
use Mush\Player\Entity\Dto\UpdatePersonalNotesTabsRequest;
use Mush\Player\Entity\PersonalNotesTab;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\UseCase\DeletePlayerNotificationUseCase;
use Mush\Player\Voter\PlayerVoter;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\User\Entity\User;
use Mush\User\Voter\UserVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/player')]
class PlayerController extends AbstractGameController
{
    use ErrorHandlerTrait;

    private EntityManagerInterface $entityManager;
    private PlayerServiceInterface $playerService;
    private CycleServiceInterface $cycleService;
    private ValidatorInterface $validator;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private DeletePlayerNotificationUseCase $deletePlayerNotification;

    private ModerationServiceInterface $moderationService;

    public function __construct(
        AdminServiceInterface $adminService,
        EntityManagerInterface $entityManager,
        PlayerServiceInterface $playerService,
        CycleServiceInterface $cycleStrategyService,
        ValidatorInterface $validator,
        ChooseSkillUseCase $chooseSkillUseCase,
        DeletePlayerNotificationUseCase $deletePlayerNotification,
        ModerationServiceInterface $moderationService,
    ) {
        parent::__construct($adminService);
        $this->entityManager = $entityManager;
        $this->playerService = $playerService;
        $this->cycleService = $cycleStrategyService;
        $this->validator = $validator;
        $this->chooseSkillUseCase = $chooseSkillUseCase;
        $this->deletePlayerNotification = $deletePlayerNotification;
        $this->moderationService = $moderationService;
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getPlayerAction(Player $player): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        // Always needed so any player triggers exploration steps : do not remove it!
        // Please increment the number of times you tried to implement an automated test at API level to remove this comment but failed
        // Counter: 3
        $this->handleCycleChange($player);

        return $this->json($player, Response::HTTP_OK, [], ['currentPlayer' => $player]);
    }

    #[Route('', methods: ['POST'])]
    public function createPlayerAction(PlayerCreateRequest $playerCreateRequest): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        if (\count($violations = $this->validator->validate($playerCreateRequest))) {
            return $this->json($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_CREATE);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        $daedalus = $playerCreateRequest->getDaedalus();
        if (!$daedalus) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'No Daedalus found.');
        }

        if ($daedalus->isDaedalusOrExplorationChangingCycle()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        if ($daedalus->getDaedalusInfo()->isDaedalusFinished()) {
            return $this->json(["Can't create player : Daedalus is already finished"], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $character = $playerCreateRequest->getCharacter();
        if (!$character) {
            return $this->json(['invalid parameters'], 422);
        }

        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerService->createPlayer($daedalus, $user, $character);

        return $this->json($player, Response::HTTP_CREATED, [], ['currentPlayer' => $player]);
    }

    #[Route('/{player}/end', methods: ['POST'])]
    public function endPlayerAction(#[MapRequestPayload] PlayerEndRequest $request, Player $player): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        if (\count($violations = $this->validator->validate($request))) {
            return $this->json($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_END, $player);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        if ($player->getPlayerInfo()->getGameStatus() !== GameStatusEnum::FINISHED) {
            return $this->json(['message' => 'Player cannot end game'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var string $message */
        $message = $request->getMessage();
        $likedPlayers = $request->getLikedPlayers();
        $this->playerService->endPlayer($player, $message, $likedPlayers);

        return $this->json(null, 200);
    }

    #[Route('/{id}/cycle-change', methods: ['GET'])]
    public function triggerCycleChange(Player $player): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        $result = $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $player->getDaedalus());

        if ($result->hasDaedalusCycleElapsed()) {
            return $this->json(['message' => 'Daedalus cycle change(s) triggered successfully (' . $result->daedalusCyclesElapsed . ' cycle(s) elapsed)'], Response::HTTP_OK);
        }
        if ($result->hasExplorationCycleElapsed()) {
            return $this->json(['message' => 'Exploration cycle change(s) triggered successfully (' . $result->explorationCyclesElapsed . ' cycle(s) elapsed)'], Response::HTTP_OK);
        }

        return $this->json(['message' => 'No cycle change triggered'], Response::HTTP_OK);
    }

    #[Route('/{id}/exploration-cycle-change', methods: ['GET'])]
    public function triggerExplorationCycleChange(Player $player): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        if (!$player->isExploring()) {
            return $this->json(['message' => 'You have to be in an exploration to do that!'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($player->getDaedalus()->isCycleChange()) {
            return $this->json(['message' => 'Daedalus is changing cycle'], Response::HTTP_CONFLICT);
        }
        $result = $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $player->getDaedalus());

        if ($result->noCycleElapsed()) {
            return $this->json(['message' => 'No cycle change triggered'], Response::HTTP_OK);
        }

        return $this->json(['message' => 'Exploration cycle change(s) triggered successfully (' . $result->explorationCyclesElapsed . ' cycle(s) elapsed)'], Response::HTTP_OK);
    }

    #[Route('/{id}/choose-skill', methods: ['POST'])]
    public function chooseSkillEndpoint(Request $request, Player $player): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        $this->chooseSkillUseCase->execute(ChooseSkillDto::createFromRequest($request, $player));

        return $this->json(['detail' => 'Skill selected successfully'], Response::HTTP_CREATED);
    }

    #[Route('/{id}/notification', methods: ['DELETE'])]
    public function deleteNotificationEndpoint(Player $player): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        $this->deletePlayerNotification->execute($player->getFirstNotificationOrThrow());

        return $this->json(['detail' => 'Notification deleted successfully'], Response::HTTP_OK);
    }

    #[Route('/{id}/notes/tabs', methods: ['PUT'])]
    public function updatePersonalNotesTabs(#[MapRequestPayload] UpdatePersonalNotesTabsRequest $request, Player $player): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        $this->denyAccessUnlessGranted(PlayerVoter::PLAYER_VIEW, $player);
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You have to accept the rules to play the game.');

        if (\count($violations = $this->validator->validate($request))) {
            return $this->json($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $personalNotes = $player->getPersonalNotes();

        // Retrieve the list of tabs IDs in the request body
        $tabsId = array_map(
            static fn ($tab) => $tab['id'],
            array_filter($request->getTabs(), static fn ($tab) => isset($tab['id']))
        );

        // Delete any existing tabs with an ID that is not in the request body
        foreach ($personalNotes->getTabs() as $tab) {
            if (!\in_array($tab->getId(), $tabsId, true)) {
                $personalNotes->removeTab($tab);
            }
        }

        // Save the tabs in the request body by updating existing tabs (where `id` is set) or
        // creating new tabs.
        foreach ($request->getTabs() as $tabData) {
            // Update existing tabs
            if (isset($tabData['id'])) {
                $tab = $personalNotes->getTabFromId($tabData['id']);
                if (!$tab) {  // The referenced tab ID doesn't exist
                    return $this->json(['error' => "Tab with id {$tabData['id']} not found"], Response::HTTP_NOT_FOUND);
                }
                $tab->setIcon($tabData['icon']);
                $tab->setIndex($tabData['index']);
                $tab->setContent($tabData['content']);
            }

            // Create new tabs
            else {
                $newTab = new PersonalNotesTab($personalNotes, $tabData['icon'], $tabData['content'], $tabData['index']);
                $personalNotes->addTab($newTab);
            }
        }

        $this->entityManager->persist($personalNotes);
        $this->entityManager->flush();

        return $this->json($personalNotes, Response::HTTP_OK);
    }

    private function handleCycleChange(Player $player): void
    {
        $daedalus = $player->getDaedalus();
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);
    }
}
