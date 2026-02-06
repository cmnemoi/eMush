<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\MetaGame\Entity\Poll\Poll;
use Mush\MetaGame\Entity\Poll\PollOption;
use Mush\MetaGame\Entity\Poll\Vote;
use Mush\User\Entity\User;
use Mush\User\Service\UserServiceInterface;

class PollService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserServiceInterface $userService,
    ) {}

    public function createPoll(string $title, int $maxVote = 1, bool $isImportant = false): Poll
    {
        $poll = new Poll($title, $maxVote, $isImportant);
        $this->entityManager->persist($poll);
        $this->entityManager->flush();

        return $poll;
    }

    public function createOption(Poll $poll, string $name): PollOption
    {
        $option = new PollOption($poll, $name);
        $this->entityManager->persist($poll);
        $this->entityManager->flush();

        return $option;
    }

    public function vote(Poll $poll, PollOption $pollOption, User $user): Vote
    {
        $createdAt = $poll->getCreatedAt();
        if ($createdAt === null) {
            throw new \LogicException('Should have a creation date');
        }

        if ($poll->isImportant() && !$this->userService->userCanVoteInImportantPoll($user, $createdAt)) {
            throw new \LogicException("user can't vote");
        }

        if (!$poll->canUserVote($user)) {
            throw new \LogicException('user voted too many times already');
        }

        if ($pollOption->userAlreadyVoted($user)) {
            throw new \LogicException('user already voted that');
        }

        $vote = new Vote($pollOption, $user);
        $this->entityManager->persist($pollOption);
        $this->entityManager->persist($vote);
        $this->entityManager->flush();

        return $vote;
    }

    public function removeVotes(Poll $poll, User $user)
    {
        $poll->removeVotesForUser($user);
        $this->entityManager->persist($poll);
        $this->entityManager->flush();
    }

    public function closePoll(Poll $poll)
    {
        $poll->close();
        $this->entityManager->persist($poll);
        $this->entityManager->flush();
    }
}
