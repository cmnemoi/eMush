<?php

declare(strict_types=1);

namespace Mush\Chat\Controller;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Dto\CreateMessage;
use Mush\Chat\Entity\Message;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Chat\Services\MessageServiceInterface;
use Mush\Chat\Specification\SpecificationInterface;
use Mush\Chat\Voter\ChannelVoter;
use Mush\Chat\Voter\MessageVoter;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Controller\AbstractGameController;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\UseCase\GetUserCurrentPlayerUseCase;
use Mush\User\Entity\User;
use Mush\User\Voter\UserVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/channel')]
class ChannelController extends AbstractGameController
{
    private const int TIME_LIMIT = 48;

    public function __construct(
        AdminServiceInterface $adminService,
        private SpecificationInterface $canCreateChannel,
        private ChannelServiceInterface $channelService,
        private MessageServiceInterface $messageService,
        private PlayerServiceInterface $playerService,
        private ValidatorInterface $validator,
        private CycleServiceInterface $cycleService,
        private TranslationServiceInterface $translationService,
        private PlayerInfoRepository $playerInfoRepository,
        private GetUserCurrentPlayerUseCase $getUserCurrentPlayer,
    ) {
        parent::__construct($adminService);
    }

    /**
     * Create a channel.
     */
    #[Route('', methods: ['POST'])]
    public function createChannelAction(): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        if (!$player->isAlive()) {
            return $this->json(['canCreate' => false], 200);
        }

        $daedalus = $player->getDaedalus();
        if ($daedalus->isDaedalusOrExplorationChangingCycle()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        if (!$this->canCreateChannel->isSatisfied($player)) {
            return $this->json(['error' => 'cannot create new channels'], 422);
        }

        $channel = $this->channelService->createPrivateChannel($player);

        return $this->json($channel, 201, [], ['currentPlayer' => $player]);
    }

    /**
     * Check if a new private channel can be created.
     */
    #[Route('/canCreatePrivate', methods: ['GET'])]
    public function canCreateChannelAction(): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        if (!$player->isAlive()) {
            return $this->json(['canCreate' => false], 200);
        }

        $daedalus = $player->getDaedalus();

        if (!$this->canCreateChannel->isSatisfied($player)) {
            $canCreate = [
                'canCreate' => false,
            ];
        } else {
            $canCreate = [
                'canCreate' => true,
                'name' => $this->translationService->translate('new.name', [], 'chat', $daedalus->getLanguage()),
                'description' => $this->translationService->translate('new.description', [], 'chat', $daedalus->getLanguage()),
            ];
        }

        return $this->json($canCreate, 201);
    }

    /**
     * Get the channels.
     */
    #[Route('', methods: ['GET'])]
    public function getChannelsActions(): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        $channels = $this->channelService->getPlayerChannels($player);

        return $this->json($channels, 200, [], ['currentPlayer' => $player]);
    }

    /**
     * Get the pirated channels.
     */
    #[Route('/pirated', methods: ['GET'])]
    public function getPiratedChannelsActions(): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        $piratedPlayer = $this->channelService->getPiratedPlayer($player);

        if ($piratedPlayer !== null) {
            $channels = $this->channelService->getPiratedChannels($piratedPlayer);

            return $this->json($channels, 200, [], ['currentPlayer' => $player, 'piratedPlayer' => $piratedPlayer]);
        }

        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Invite player to a channel.
     */
    #[Route('/{channel}/invite', methods: ['POST'])]
    public function inviteAction(Request $request, Channel $channel): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $playerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user);

        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        $invited = $request->getPayload()->get('player');

        /** @var Daedalus $daedalus */
        $daedalus = $channel->getDaedalusInfo()->getDaedalus();
        if ($daedalus->isDaedalusOrExplorationChangingCycle()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        if ($invited === null) {
            return $this->json(['error' => 'player not found'], 404);
        }

        if (!($invitedPlayer = $this->playerService->findById((int) $invited))) {
            return $this->json(['error' => 'player not found'], 404);
        }

        if ($invitedPlayer->getDaedalus() !== $daedalus) {
            return $this->json(['error' => 'player is not from this daedalus'], 422);
        }

        if (!$this->canCreateChannel->isSatisfied($invitedPlayer)) {
            return $this->json(['error' => 'player cannot open a new channel'], 422);
        }

        if ($channel->isPlayerParticipant($invitedPlayer->getPlayerInfo())) {
            return $this->json(['error' => 'player is already in the channel'], 422);
        }

        $channel = $this->channelService->invitePlayer($invitedPlayer, $channel);

        return $this->json($channel, 200, [], ['currentPlayer' => $playerInfo?->getPlayer()]);
    }

    /**
     * Get invitable player to the channel.
     */
    #[Route('/{channel}/invite', methods: ['GET'])]
    public function getInvitablePlayerAction(Request $request, Channel $channel): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        /** @var User $user */
        $user = $this->getUser();

        $playerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user);
        if ($playerInfo === null) {
            return $this->json(['error' => 'player not found'], 422);
        }

        $playerInfo = $playerInfo->getPlayer();
        if ($playerInfo === null) {
            return $this->json(['error' => 'player not found'], 422);
        }

        /** @var Daedalus $daedalus */
        $daedalus = $channel->getDaedalusInfo()->getDaedalus();
        if ($daedalus !== $playerInfo->getDaedalus()) {
            return $this->json(['error' => 'player is not from this daedalus'], 422);
        }

        return $this->json(
            $this->channelService->getInvitablePlayersToPrivateChannel($channel, $playerInfo),
            200
        );
    }

    /**
     * Exit a channel.
     */
    #[Route('/{channel}/exit', methods: ['POST'])]
    public function exitAction(Channel $channel): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        if ($channel->getDaedalusInfo()->getDaedalus() !== $player->getDaedalus()) {
            return $this->json(['error' => 'player is not from this daedalus'], 422);
        }

        $daedalus = $player->getDaedalus();
        if ($daedalus->isDaedalusOrExplorationChangingCycle()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus is changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        $this->channelService->exitChannel($player, $channel);

        return $this->json(null, 200);
    }

    /**
     * Create a message in the channel.
     */
    #[Route('/{channel}/message', methods: ['POST'])]
    public function createMessageAction(CreateMessage $messageCreate, Channel $channel): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var Daedalus $daedalus */
        $daedalus = $channel->getDaedalusInfo()->getDaedalus();
        if ($daedalus->isDaedalusOrExplorationChangingCycle()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        $messageCreate->setChannel($channel);

        $this->denyAccessUnlessGranted(ChannelVoter::POST, $channel);

        if (\count($violations = $this->validator->validate($messageCreate))) {
            return $this->json($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $parentMessage = $messageCreate->getParent();
        if ($parentMessage && $parentMessage->getChannel() !== $channel) {
            return $this->json(['error' => 'invalid parent message'], 422);
        }

        /** @var User $user */
        $user = $this->getUser();
        $userPlayer = $this->getUserCurrentPlayer->execute($user);

        if (!$messageCreate->sentByPlayer($userPlayer)) {
            throw new ConflictHttpException('You are sending a message under the name of another player.');
        }

        $this->messageService->createPlayerMessage($userPlayer, $messageCreate);

        $messages = $this->messageService->getChannelMessages($userPlayer, $channel, $messageCreate->getTimeLimit());

        return $this->json($messages, 200, [], ['currentPlayer' => $userPlayer]);
    }

    /**
     * Get channel messages.
     */
    #[Route('/{channel}/message', methods: ['GET'])]
    public function getMessages(Request $request, Channel $channel): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        /** @var Daedalus $daedalus */
        $daedalus = $channel->getDaedalusInfo()->getDaedalus();

        // @TODO: move this to a Voter
        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        if ($channel->getDaedalusInfo()->getDaedalus() !== $player->getDaedalus()) {
            return $this->json(['error' => 'player is not from this daedalus'], 422);
        }

        $timeLimit = new \DateInterval(\sprintf('PT%dH', $request->query->getInt('timeLimit', self::TIME_LIMIT)));

        $messages = $this->messageService->getChannelMessages($player, $channel, $timeLimit);

        return $this->json($messages, 200, [], ['currentPlayer' => $player]);
    }

    /**
     * Mark a message as read.
     */
    #[Route('/read-message/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function readMessageAction(Message $message): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        $channel = $message->getChannel();
        if ($channel->getDaedalusInfo()->getDaedalus() !== $player->getDaedalus()) {
            return $this->json(['error' => 'You are not from this Daedalus!'], Response::HTTP_FORBIDDEN);
        }

        $this->messageService->markMessageAsReadForPlayer($message, $player);

        return $this->json(['detail' => 'Message marked as read successfully'], Response::HTTP_OK);
    }

    /**
     * Put a message in favorites.
     */
    #[Route('/favorite-message/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(MessageVoter::FAVORITE, subject: 'message')]
    public function favoriteMessageAction(Message $message): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $player = $this->getUserPlayer($user);

        $this->messageService->putMessageInFavoritesForPlayer($message, $player);

        return $this->json(['detail' => 'Message marked as favorites successfully'], Response::HTTP_OK);
    }

    /**
     * Remove a message from favorites.
     */
    #[Route('/unfavorite-message/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function unfavoriteMessageAction(Message $message): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        $channel = $message->getChannel();
        if ($channel->getDaedalusInfo()->getDaedalus() !== $player->getDaedalus()) {
            return $this->json(['error' => 'You are not from this Daedalus!'], Response::HTTP_FORBIDDEN);
        }

        $this->messageService->removeMessageFromFavoritesForPlayer($message, $player);

        return $this->json(['detail' => 'Message removed from favorites successfully'], Response::HTTP_OK);
    }

    /**
     * Get player favorites channel.
     */
    #[Route('/favorites', methods: ['GET'])]
    public function getFavoritesChannelAction(): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        if ($player->getFavoriteMessages()->isEmpty() || !$this->channelService->canPlayerCommunicate($player)) {
            return $this->json(null, Response::HTTP_NO_CONTENT);
        }

        $favoritesChannel = $this->channelService->getPublicChannel($player->getDaedalusInfo());

        return $this->json($favoritesChannel, Response::HTTP_OK, [], ['currentPlayer' => $player, 'favorite' => true]);
    }

    /**
     * Get favorites channel messages.
     */
    #[Route('/favorites/messages', methods: ['GET'])]
    public function getFavoritesChannelMessages(): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        return $this->json($this->messageService->getPlayerFavoritesChannelMessages($player), Response::HTTP_OK, [], ['currentPlayer' => $player]);
    }

    /**
     * Mark a channel as read.
     */
    #[Route('/{id}/read', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function markChannelAsReadForPlayerAction(Channel $channel): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $this->getUserPlayer($user);

        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        $this->channelService->markChannelAsReadForPlayer($channel, $player);

        return $this->json(['detail' => 'Channel marked as read successfully'], Response::HTTP_OK);
    }

    private function getUserPlayer(User $user): Player
    {
        $player = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user)?->getPlayer();
        if (!$player) {
            throw new AccessDeniedException('In game user should have a player');
        }

        return $player;
    }
}
