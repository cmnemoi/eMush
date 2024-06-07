<?php

namespace Mush\Communication\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Communication\Specification\SpecificationInterface;
use Mush\Communication\Voter\ChannelVoter;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Controller\AbstractGameController;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Entity\User;
use Mush\User\Voter\UserVoter;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UsersController.
 *
 * @Route(path="/channel")
 */
class ChannelController extends AbstractGameController
{
    private SpecificationInterface $canCreateChannel;
    private ChannelServiceInterface $channelService;
    private MessageServiceInterface $messageService;
    private PlayerServiceInterface $playerService;
    private ValidatorInterface $validator;
    private CycleServiceInterface $cycleService;
    private TranslationServiceInterface $translationService;
    private PlayerInfoRepository $playerInfoRepository;

    public function __construct(
        AdminServiceInterface $adminService,
        SpecificationInterface $canCreateChannel,
        ChannelServiceInterface $channelStrategyService,
        MessageServiceInterface $messageService,
        PlayerServiceInterface $playerService,
        ValidatorInterface $validator,
        CycleServiceInterface $cycleService,
        TranslationServiceInterface $translationService,
        PlayerInfoRepository $playerInfoRepository
    ) {
        parent::__construct($adminService);
        $this->canCreateChannel = $canCreateChannel;
        $this->channelService = $channelStrategyService;
        $this->messageService = $messageService;
        $this->playerService = $playerService;
        $this->validator = $validator;
        $this->cycleService = $cycleService;
        $this->translationService = $translationService;
        $this->playerInfoRepository = $playerInfoRepository;
    }

    /**
     * Create a channel.
     *
     * @OA\Tag(name="Channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="")
     */
    public function createChannelAction(): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        if (!$player->isAlive()) {
            return $this->view(['canCreate' => false], 200);
        }

        $daedalus = $player->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        if (!$this->canCreateChannel->isSatisfied($player)) {
            return $this->view(['error' => 'cannot create new channels'], 422);
        }

        $context = new Context();
        $context
            ->setAttribute('currentPlayer', $player);

        $channel = $this->channelService->createPrivateChannel($player);

        $view = $this->view($channel, 201);
        $view->setContext($context);

        return $view;
    }

    /**
     * Check if a new private channel can be created.
     *
     * @OA\Tag(name="Channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/canCreatePrivate")
     */
    public function canCreateChannelAction(): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        if (!$player->isAlive()) {
            return $this->view(['canCreate' => false], 200);
        }

        $daedalus = $player->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

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

        return $this->view($canCreate, 201);
    }

    /**
     * Get the channels.
     *
     * @OA\Tag(name="Channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="")
     */
    public function getChannelsActions(): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        $daedalus = $player->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        $channels = $this->channelService->getPlayerChannels($player);
        $context = new Context();
        $context->setAttribute('currentPlayer', $player);

        $view = $this->view($channels, 200);
        $view->setContext($context);

        return $view;
    }

    /**
     * Get the pirated channels.
     *
     * @OA\Tag(name="channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get (path="/pirated")")
     */
    public function getPiratedChannelsActions(): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        $daedalus = $player->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        $piratedPlayer = $this->channelService->getPiratedPlayer($player);

        if ($piratedPlayer !== null) {
            $channels = $this->channelService->getPiratedChannels($piratedPlayer);

            $context = new Context();
            $context
                ->setAttribute('currentPlayer', $player)
                ->setAttribute('piratedPlayer', $piratedPlayer);

            $view = $this->view($channels, 200);
            $view->setContext($context);

            return $view;
        }

        return $this->view([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Invite player to a channel.
     *
     *    @OA\RequestBody (
     *      description="Input data format",
     *
     *      @OA\MediaType(
     *          mediaType="application/json",
     *
     *          @OA\Schema(
     *              type="object",
     *
     *              @OA\Property(
     *                  type="int",
     *                  property="player",
     *                  description="id of the player to invite"
     *              )
     *          )
     *      )
     *    )
     *
     * @OA\Tag(name="Channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/{channel}/invite")
     */
    public function inviteAction(Request $request, Channel $channel): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $playerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user);

        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        $invited = $request->get('player');

        /** @var Daedalus $daedalus */
        $daedalus = $channel->getDaedalusInfo()->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        if (!($invitedPlayer = $this->playerService->findById($invited))) {
            return $this->view(['error' => 'player not found'], 404);
        }

        if ($invitedPlayer->getDaedalus() !== $daedalus) {
            return $this->view(['error' => 'player is not from this daedalus'], 422);
        }

        if (!$this->canCreateChannel->isSatisfied($invitedPlayer)) {
            return $this->view(['error' => 'player cannot open a new channel'], 422);
        }

        if ($channel->isPlayerParticipant($invitedPlayer->getPlayerInfo())) {
            return $this->view(['error' => 'player is already in the channel'], 422);
        }

        $channel = $this->channelService->invitePlayer($invitedPlayer, $channel);

        $context = new Context();
        $context->setAttribute('currentPlayer', $playerInfo?->getPlayer());

        $view = $this->view($channel, 200);
        $view->setContext($context);

        return $view;
    }

    /**
     * Get invitable player to the channel.
     *
     * @OA\Tag(name="Channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/{channel}/invite")
     */
    public function getInvitablePlayerAction(Request $request, Channel $channel): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $playerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user);

        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        /** @var Daedalus $daedalus */
        $daedalus = $channel->getDaedalusInfo()->getDaedalus();
        if ($daedalus !== $playerInfo?->getPlayer()->getDaedalus()) {
            return $this->view(['error' => 'player is not from this daedalus'], 422);
        }

        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        return $this->view(
            $this->channelService->getInvitablePlayersToPrivateChannel($channel, $playerInfo?->getPlayer()),
            200
        );
    }

    /**
     * exit a channel.
     *
     * @OA\Tag(name="Channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/{channel}/exit")
     */
    public function exitAction(Channel $channel): View
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
            return $this->view(['error' => 'player is not from this daedalus'], 422);
        }

        $daedalus = $player->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        $this->channelService->exitChannel($player, $channel);

        return $this->view(null, 200);
    }

    /**
     * Create a message in the channel.
     *
     * @OA\Tag(name="Channel")
     *
     *    @OA\RequestBody (
     *      description="Input data format",
     *
     *      @OA\MediaType(
     *          mediaType="application/json",
     *
     *          @OA\Schema(
     *              type="object",
     *
     *              @OA\Property(
     *                  type="integer",
     *                  property="parent",
     *                  description="The parent message"
     *              ),
     *              @OA\Property(
     *                  type="string",
     *                  property="message",
     *                  description="The message"
     *              ),
     *              @OA\Property(
     *                  type="integer",
     *                  property="player",
     *                  description="id of the player sending message"
     *              ),
     *              @OA\Property(
     *                  type="integer",
     *                  property="page",
     *                  description="page number"
     *              ),
     *              @OA\Property(
     *                  type="integer",
     *                  property="limit",
     *                  description="number of messages per page"
     *              )
     *          )
     *      )
     *    )
     *
     * @ParamConverter("messageCreate", converter="MessageCreateParamConverter")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/{channel}/message")
     */
    public function createMessageAction(CreateMessage $messageCreate, Channel $channel): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var Daedalus $daedalus */
        $daedalus = $channel->getDaedalusInfo()->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        $messageCreate->setChannel($channel);

        $this->denyAccessUnlessGranted(ChannelVoter::POST, $channel);

        if (\count($violations = $this->validator->validate($messageCreate))) {
            return $this->view($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $parentMessage = $messageCreate->getParent();
        if (
            !$channel->isFavorites()
            && $parentMessage
            && $parentMessage->getChannel() !== $channel
        ) {
            return $this->view(['error' => 'invalid parent message'], 422);
        }

        /** @var User $user */
        $user = $this->getUser();

        /** @var PlayerInfo $currentPlayerInfo */
        $currentPlayerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user);

        /** @var Player $currentPlayer */
        $currentPlayer = $currentPlayerInfo->getPlayer();

        // in case of a pirated talkie, the message can be sent under the name of another player
        $playerMessage = $messageCreate->getPlayer();
        if (!$playerMessage) {
            $playerMessage = $currentPlayer;
        }

        $this->messageService->createPlayerMessage($playerMessage, $messageCreate);
        if ($channel->isFavorites()) {
            $messages = $this->messageService->getPlayerFavoritesChannelMessages($currentPlayer, $messageCreate->getPage(), $messageCreate->getLimit());
        } else {
            $messages = $this->messageService->getChannelMessages($currentPlayer, $channel, $messageCreate->getPage(), $messageCreate->getLimit());
        }

        $context = new Context();
        $context->setAttribute('currentPlayer', $currentPlayer);

        $view = $this->view($messages, 200);
        $view->setContext($context);

        return $view;
    }

    /**
     * Get channel messages.
     *
     * @OA\Tag(name="Channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get (path="/{channel}/message")
     */
    public function getMessages(Request $request, Channel $channel): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }
        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        /** @var Daedalus $daedalus */
        $daedalus = $channel->getDaedalusInfo()->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        // @TODO: move this to a Voter
        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        if ($channel->getDaedalusInfo()->getDaedalus() !== $player?->getDaedalus()) {
            return $this->view(['error' => 'player is not from this daedalus'], 422);
        }

        $page = (int) $request->get('page');
        $limit = (int) $request->get('limit');

        $messages = $this->messageService->getChannelMessages($player, $channel, $page, $limit);

        $context = new Context();
        $context->setAttribute('currentPlayer', $player);

        $view = $this->view($messages, 200);
        $view->setContext($context);

        return $view;
    }

    /**
     * Mark a message as read.
     *
     * @OA\Tag(name="Channel")
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The message id",
     *      required=true
     * )
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch (path="/read-message/{id}", requirements={"id"="\d+"})
     */
    public function readMessageAction(Message $message): View
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
            return $this->view(['error' => 'You are not from this Daedalus!'], Response::HTTP_FORBIDDEN);
        }

        $this->messageService->markMessageAsReadForPlayer($message, $player);

        return $this->view(['detail' => 'Message marked as read successfully'], Response::HTTP_OK);
    }

    /**
     * Put a message in favorites.
     *
     * @OA\Tag(name="Channel")
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The message id",
     *      required=true
     * )
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post (path="/favorite-message/{id}", requirements={"id"="\d+"})
     */
    public function favoriteMessageAction(Message $message): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        $channel = $message->getChannel();
        if ($channel->getDaedalusInfo()->getDaedalus() !== $player?->getDaedalus()) {
            return $this->view(['error' => 'You are not from this Daedalus!'], Response::HTTP_FORBIDDEN);
        }

        $this->messageService->putMessageInFavoritesForPlayer($message, $player);

        return $this->view(['detail' => 'Message marked as favorites successfully'], Response::HTTP_OK);
    }

    /**
     * Remove a message from favorites.
     *
     * @OA\Tag(name="Channel")
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The message id",
     *      required=true
     * )
     *
     * @Security(name="Bearer")
     *
     * @Rest\Delete (path="/unfavorite-message/{id}", requirements={"id"="\d+"})
     */
    public function unfavoriteMessageAction(Message $message): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        $channel = $message->getChannel();
        if ($channel->getDaedalusInfo()->getDaedalus() !== $player?->getDaedalus()) {
            return $this->view(['error' => 'You are not from this Daedalus!'], Response::HTTP_FORBIDDEN);
        }

        $this->messageService->removeMessageFromFavoritesForPlayer($message, $player);

        return $this->view(['detail' => 'Message removed from favorites successfully'], Response::HTTP_OK);
    }

    /**
     * Get player favorites channel.
     *
     * @OA\Tag(name="Channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/favorites")
     */
    public function getFavoritesChannelAction(): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        $daedalus = $player->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        if ($player->getFavoriteMessages()->isEmpty() || !$this->channelService->canPlayerCommunicate($player)) {
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        $favoritesChannel = $this->channelService->getPlayerFavoritesChannel($player);

        $context = new Context();
        $context->setAttribute('currentPlayer', $player);

        $view = $this->view($favoritesChannel, Response::HTTP_OK);
        $view->setContext($context);

        return $view;
    }

    /**
     * Get favorites channel messages.
     *
     * @OA\Tag(name="Channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get (path="/favorites/messages")
     */
    public function getFavoritesChannelMessages(Request $request): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(UserVoter::USER_IN_GAME, $user);
        $player = $this->getUserPlayer($user);

        /** @var Daedalus $daedalus */
        $daedalus = $player->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        $context = new Context();
        $context->setAttribute('currentPlayer', $player);

        $page = (int) $request->get('page');
        $limit = (int) $request->get('limit');

        $view = $this->view($this->messageService->getPlayerFavoritesChannelMessages($player, $page, $limit), Response::HTTP_OK);
        $view->setContext($context);

        return $view;
    }

    /**
     * Mark a channel as read.
     *
     * @OA\Tag(name="Channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch (path="/{id}/read", requirements={"id"="\d+"})
     */
    public function markChannelAsReadForPlayerAction(Channel $channel): View
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $this->getUserPlayer($user);

        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        $this->channelService->markChannelAsReadForPlayer($channel, $player);

        return $this->view(['detail' => 'Channel marked as read successfully'], Response::HTTP_OK);
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
