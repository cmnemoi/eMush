<?php

declare(strict_types=1);

namespace Mush\Communication\ParamConverter;

use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AutoconfigureTag('controller.argument_value_resolver', ['priority' => 150])]
final class MessageCreateParamConverter implements ValueResolverInterface
{
    private const int TIME_LIMIT = 48;

    private MessageServiceInterface $messageService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        MessageServiceInterface $messageService,
        PlayerServiceInterface $playerService
    ) {
        $this->messageService = $messageService;
        $this->playerService = $playerService;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($this->supports($argument)) {
            return $this->apply($request);
        }

        return [];
    }

    public function supports(ArgumentMetadata $argument): bool
    {
        return CreateMessage::class === $argument->getType();
    }

    /**
     * @return array<int, CreateMessage>
     */
    private function apply(Request $request): array
    {
        $message = $request->request->get('message');
        $parent = $request->request->get('parent');
        $playerId = $request->request->get('player');
        $timeLimit = (int) $request->request->get('timeLimit', self::TIME_LIMIT);

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
            ->setTimeLimit(new \DateInterval(\sprintf('PT%dH', $timeLimit)));

        return [$messageCreate];
    }
}
