<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Mush\MetaGame\Entity\Poll\Poll;
use Mush\MetaGame\Entity\Poll\PollOption;
use Mush\MetaGame\Service\PollService;
use Nelmio\ApiDocBundle\Attribute\Security as NelmioSecurity;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class for actions that concern polls.
 *
 * @Route(path="/poll")
 *
 * @OA\Tag(name="Poll")
 */
final class PollActionsController extends AbstractFOSRestController
{
    private const MAX_OPTION = 25;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PollService $pollService,
        private LockFactory $lockFactory,
    ) {}

    /**
     * Create an important poll to be used in a news.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Post(path: '/create-admin-poll')]
    #[NelmioSecurity(name: 'Bearer')]
    public function createPollAdmin(Request $request): View
    {
        $title = $request->getPayload()->getString('title');
        $maxVote = $request->getPayload()->getInt('maxVotes');

        $poll = $this->pollService->createPoll($title, $maxVote, true);

        $optionCount = 0;

        while ($optionCount < self::MAX_OPTION) {
            if ($request->getPayload()->has('option' . $optionCount)) {
                $this->pollService->createOption($poll, $request->getPayload()->getString('option' . $optionCount));
            }
            ++$optionCount;
        }

        return new View($poll, Response::HTTP_OK);
    }

    /**
     * Get a poll.
     */
    #[Get(path: '/poll/{poll}')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getPoll(Poll $poll): View
    {
        return new View($poll, Response::HTTP_OK);
    }

    /**
     * Vote in a poll.
     */
    #[Post(path: '/vote/{poll}')]
    #[NelmioSecurity(name: 'Bearer')]
    public function vote(Poll $poll, Request $request): View
    {
        $lock = $this->lockFactory->createLock('poll_' . $poll->getId());

        if ($lock->acquire()) {
            $user = $this->getUser();

            $remainingVotes = $poll->getRemainingsVotes($user);
            $optionCount = 0;

            while ($optionCount < $remainingVotes) {
                if ($request->getPayload()->has('option' . $optionCount)) {
                    $option = $this->entityManager->getReference(PollOption::class, $request->getPayload()->getInt('option' . $optionCount));
                    if ($option) {
                        $this->pollService->vote($poll, $option, $user);
                    }
                }
                ++$optionCount;
            }

            $lock->release();
        }

        $context = new Context()
            ->setAttribute('user', $this->getUser());

        return new View($poll, Response::HTTP_OK)->setContext($context);
    }

    /**
     * Remove the user votes.
     */
    #[Post(path: '/remove-votes/{poll}')]
    #[NelmioSecurity(name: 'Bearer')]
    public function removeVotes(Poll $poll, Request $request): View
    {
        $lock = $this->lockFactory->createLock('poll_' . $poll->getId());

        if ($lock->acquire()) {
            $user = $this->getUser();
            $this->pollService->removeVotes($poll, $user);

            $lock->release();
        }

        return new View($poll, Response::HTTP_OK);
    }

    /**
     * Close a poll so that no one can vote anymore.
     */
    #[Post(path: '/close-poll/{poll}')]
    #[NelmioSecurity(name: 'Bearer')]
    #[IsGranted('ROLE_ADMIN')]
    public function closePoll(Poll $poll, Request $request): View
    {
        $lock = $this->lockFactory->createLock('poll_' . $poll->getId());

        if ($lock->acquire()) {
            $this->pollService->closePoll($poll);

            $lock->release();
        }

        return new View($poll, Response::HTTP_OK);
    }
}
