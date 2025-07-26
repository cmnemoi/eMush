<?php

declare(strict_types=1);

namespace Mush\Chat\Services;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Message;
use Mush\Chat\Gateway\NeronAnswerGatewayInterface;
use Mush\Chat\Repository\MessageRepositoryInterface;

final class CreateNeronAnswerToQuestionService
{
    public function __construct(
        private MessageRepositoryInterface $messageRepository,
        private NeronAnswerGatewayInterface $neronAnswer,
    ) {}

    public function execute(string $question, Channel $channel): void
    {
        $answer = $this->neronAnswer->getFor($question);

        $daedalus = $channel->getDaedalusInfo()->getDaedalusOrThrow();

        $message = new Message();
        $message
            ->setMessage($answer)
            ->setChannel($channel)
            ->setNeron($daedalus->getNeron())
            ->setDay($daedalus->getDay())
            ->setCycle($daedalus->getCycle());
        $channel->addMessage($message);

        $this->messageRepository->save($message);
    }
}
