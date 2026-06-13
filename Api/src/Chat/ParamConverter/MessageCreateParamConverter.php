<?php

declare(strict_types=1);

namespace Mush\Chat\ParamConverter;

use Mush\Chat\Entity\Dto\CreateMessage;
use Mush\Chat\Services\MessageServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageCreateParamConverter implements ValueResolverInterface
{
    private const int TIME_LIMIT = 48;

    public function __construct(
        private MessageServiceInterface $messageService
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== CreateMessage::class) {
            return [];
        }

        $payload = $request->getPayload();
        $message = $payload->get('message');
        $parent = $payload->get('parent');
        $pirated = $payload->get('isPirated');
        $playerId = $payload->get('playerId');
        $timeLimit = (int) $payload->get('timeLimit', self::TIME_LIMIT);

        $parentMessage = null;
        if ($parent) {
            $parentMessage = $this->messageService->getMessageById((int) $parent);
            if ($parentMessage === null) {
                throw new NotFoundHttpException('Parent message not found');
            }
        }

        $messageCreate = new CreateMessage();
        $messageCreate
            ->setParent($parentMessage)
            ->setPlayerId((int) $playerId)
            ->setMessage((string) $message)
            ->setTimeLimit(new \DateInterval(\sprintf('PT%dH', $timeLimit)))
            ->setPirated((bool) $pirated);

        return [$messageCreate];
    }
}
