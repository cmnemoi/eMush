<?php

namespace Mush\Communication\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Communication\Specification\SpecificationInterface;
use Mush\Communication\Voter\ChannelVoter;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UsersController.
 *
 * @Route(path="/channel")
 */
class ChannelController extends AbstractFOSRestController
{
    private SpecificationInterface $canCreateChannel;
    private ChannelServiceInterface $channelService;
    private MessageServiceInterface $messageService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        SpecificationInterface $canCreateChannel,
        MessageServiceInterface $messageService,
        ChannelServiceInterface $channelService,
        PlayerServiceInterface $playerService
    ) {
        $this->canCreateChannel = $canCreateChannel;
        $this->channelService = $channelService;
        $this->messageService = $messageService;
        $this->playerService = $playerService;
    }

    /**
     * Create a channel
     *
     * @OA\Tag(name="Channel")
     * @Security(name="Bearer")
     * @Rest\Post(path="")
     */
    public function createChannelAction(): View
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getCurrentGame();
        if (!$this->canCreateChannel->isSatisfied($player)) {
            return $this->view(['error' => 'cannot create new channels'], 422);
        }

        $channel = $this->channelService->createPrivateChannel($player);

        return $this->view($channel, 201);
    }

    /**
     * Get the channels
     *
     * @OA\Tag(name="Channel")
     * @Security(name="Bearer")
     * @Rest\GET (path="")
     */
    public function getChannelsActions(): View
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getCurrentGame();
        if ($player === null) {
            return $this->view(['error' => 'cannot view channels'], 422);
        }

        $channels = $this->channelService->getPlayerChannels($player);

        return $this->view($channels, 200);
    }

    /**
     * Invite player to a channel
     *    @OA\RequestBody (
     *      description="Input data format",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  type="string",
     *                  property="character",
     *                  description="The player to invite"
     *              )
     *          )
     *      )
     *    )
     * @OA\Tag(name="Channel")
     * @Security(name="Bearer")
     * @Rest\Post(path="/{channel}/invite")
     */
    public function inviteAction(Request $request, Channel $channel): View
    {
        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        $invited = $request->get('player');

        if (!($invitedPlayer = $this->playerService->findOneByCharacter($invited))) {
            return $this->view(['error' => 'player not found'], 404);
        }

        if (!$this->canCreateChannel->isSatisfied($invitedPlayer)) {
            return $this->view(['error' => 'player cannot open a new channel'], 422);
        }

        $channel = $this->channelService->invitePlayer($invitedPlayer, $channel);

        return $this->view($channel, 200);
    }

    /**
     * exit a channel
     * @OA\Tag(name="Channel")
     * @Security(name="Bearer")
     * @Rest\Post(path="/{channel}/exit")
     */
    public function exitAction(Channel $channel): View
    {
        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getCurrentGame();

        $this->channelService->exitChannel($player, $channel);

        return $this->view(null, 200);
    }

    /**
     * Create a message in the channel
     * @OA\Tag(name="Channel")
     *    @OA\RequestBody (
     *      description="Input data format",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  type="integer",
     *                  property="parent",
     *                  description="The parent message"
     *              ),
     *              @OA\Property(
     *                  type="string",
     *                  property="message",
     *                  description="The message"
     *              )
     *          )
     *      )
     *    )
     * @Security(name="Bearer")
     * @Rest\Post(path="/{channel}/message")
     */
    public function createMessageAction(Request $request, Channel $channel): View
    {
        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        $parent = $request->get('parent');
        $message = $request->get('message');
        $parentMessage= null;

        if ($parent && !($parentMessage = $this->messageService->getMessageById($parent))) {
             return $this->view(['error' => 'parentMessageNotFound'], 404);
        }

        if ($parentMessage && $parentMessage->getChannel() !== $channel) {
            return $this->view(['error' => 'invalid parent message'], 422);
        }

        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getCurrentGame();

        $this->messageService->createPlayerMessage($player, $channel, $message, $parentMessage);
        $messages = $this->messageService->getChannelMessages($player, $channel);

        return $this->view($messages, 200);
    }


    /**
     * Get channel messages
     *
     * @OA\Tag(name="Channel")
     * @Security(name="Bearer")
     * @Rest\GET (path="/{channel}/message")
     */
    public function getMessages(Request $request, Channel $channel): View
    {
        $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);

        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getCurrentGame();

        $messages = $this->messageService->getChannelMessages($player, $channel);

        return $this->view($messages, 200);
    }
}
