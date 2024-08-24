<?php

namespace Mush\Communication\ParamConverter;

use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageCreateParamConverter implements ParamConverterInterface
{
    private MessageServiceInterface $messageService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        MessageServiceInterface $messageService,
        PlayerServiceInterface $playerService
    ) {
        $this->messageService = $messageService;
        $this->playerService = $playerService;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $message = $request->request->get('message');
        $parent = $request->request->get('parent');
        $playerId = $request->request->get('player');
        $timeLimit = (int) $request->request->get('timeLimit', 48);

        $messageCreate = new CreateMessage();
        $parentMessage = null;
        if ($parent) {
            $parentMessage = $this->messageService->getMessageById((int) $parent);
            if ($parentMessage === null) {
                throw new NotFoundHttpException('Parent message not found');
            }
        }

        $player = null;
        if ($playerId) {
            $player = $this->playerService->findById((int) $playerId);
            if ($player === null) {
                throw new NotFoundHttpException('Player not found');
            }
        }

        $messageCreate
            ->setParent($parentMessage)
            ->setMessage((string) $message)
            ->setPlayer($player)
            ->setTimeLimit(new \DateInterval(sprintf('PT%dH', $timeLimit)));

        $request->attributes->set($configuration->getName(), $messageCreate);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return CreateMessage::class === $configuration->getClass();
    }
}
