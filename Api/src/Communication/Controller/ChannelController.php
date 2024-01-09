<?php

namespace Mush\Communication\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Communication\Specification\SpecificationInterface;
use Mush\Communication\Voter\ChannelVoter;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Controller\AbstractGameController;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Entity\User;
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
        ChannelServiceInterface $channelService,
        MessageServiceInterface $messageService,
        PlayerServiceInterface $playerService,
        ValidatorInterface $validator,
        CycleServiceInterface $cycleService,
        TranslationServiceInterface $translationService,
        PlayerInfoRepository $playerInfoRepository
    ) {
        parent::__construct($adminService);
        $this->canCreateChannel = $canCreateChannel;
        $this->channelService = $channelService;
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
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);
        $player = $playerInfo->getPlayer();

        $this->denyIfPlayerNotInGame($player);

        if ($playerInfo->getGameStatus() !== GameStatusEnum::CURRENT) {
            throw new AccessDeniedException('Player is dead');
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
            ->setAttribute('currentPlayer', $player)
        ;

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
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);
        $player = $playerInfo->getPlayer();

        $this->denyIfPlayerNotInGame($player);

        if ($playerInfo->getGameStatus() === GameStatusEnum::CLOSED) {
            throw new AccessDeniedException('Player is dead');
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
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);
        $player = $playerInfo->getPlayer();

        $this->denyIfPlayerNotInGame($player);

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
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);
        $player = $playerInfo->getPlayer();

        $this->denyIfPlayerNotInGame($player);

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
                ->setAttribute('piratedPlayer', $piratedPlayer)
            ;

            $view = $this->view($channels, 200);
            $view->setContext($context);

            return $view;
        }

        return $this->view([]);
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
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);

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
        $context->setAttribute('currentPlayer', $playerInfo->getPlayer());

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
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);

        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        if ($channel->getDaedalusInfo()->getDaedalus() !== $playerInfo->getPlayer()->getDaedalus()) {
            return $this->view(['error' => 'player is not from this daedalus'], 422);
        }

        /** @var Daedalus $daedalus */
        $daedalus = $channel->getDaedalusInfo()->getDaedalus();
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        return $this->view(
            $this->channelService->getInvitablePlayersToPrivateChannel($channel, $playerInfo->getPlayer()),
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
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);
        $player = $playerInfo->getPlayer();

        $this->denyIfPlayerNotInGame($player);

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

        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        if (count($violations = $this->validator->validate($messageCreate))) {
            return $this->view($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $parentMessage = $messageCreate->getParent();

        if ($parentMessage && $parentMessage->getChannel() !== $channel) {
            return $this->view(['error' => 'invalid parent message'], 422);
        }

        /** @var User $user */
        $user = $this->getUser();
        /** @var PlayerInfo $currentPlayerInfo */
        $currentPlayerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);

        /** @var Player $currentPlayer */
        $currentPlayer = $currentPlayerInfo->getPlayer();

        // in case of a pirated talkie, the message can be sent under the name of another player
        $playerMessage = $messageCreate->getPlayer();

        if ($playerMessage === null) {
            $playerMessage = $currentPlayer;
        }

        $this->denyIfPlayerNotInGame($currentPlayer);

        $this->checkMessagePermission($currentPlayer, $channel);

        $this->messageService->createPlayerMessage($playerMessage, $messageCreate);
        $messages = $this->messageService->getChannelMessages($currentPlayer, $channel);

        $context = new Context();
        $context->setAttribute('currentPlayer', $currentPlayer);

        $view = $this->view($messages, 200);
        $view->setContext($context);

        return $view;
    }

    public function checkMessagePermission(Player $currentPlayer, Channel $channel): void
    {
        if (
            !$this->messageService->canPlayerPostMessage($currentPlayer, $channel)
            || (!$this->channelService->canPlayerWhisperInChannel($channel, $currentPlayer)
                && !$this->channelService->canPlayerCommunicate($currentPlayer))
        ) {
            throw new AccessDeniedException('Player cannot speak in this channel');
        }

        if ($channel->getDaedalusInfo()->getDaedalus() !== $currentPlayer->getDaedalus()) {
            throw new AccessDeniedException('player is not from this daedalus');
        }
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

        /** @var User $user */
        $user = $this->getUser();
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);
        $player = $playerInfo->getPlayer();

        $this->denyIfPlayerNotInGame($player);

        if ($channel->getDaedalusInfo()->getDaedalus() !== $player->getDaedalus()) {
            return $this->view(['error' => 'player is not from this daedalus'], 422);
        }

        $messages = $this->messageService->getChannelMessages($player, $channel);

        $context = new Context();
        $context->setAttribute('currentPlayer', $player);

        $view = $this->view($messages, 200);
        $view->setContext($context);

        return $view;
    }

    private function denyIfPlayerNotInGame(?Player $player): void
    {
        if ($player === null || $player->getPlayerInfo() === null) {
            throw new AccessDeniedException('User should be in game');
        }
    }
}
