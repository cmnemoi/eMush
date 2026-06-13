<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Mush\MetaGame\Entity\Poll\Poll;
use Mush\MetaGame\Entity\Poll\PollOption;
use Mush\MetaGame\Service\PollService;
use Mush\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @OA\Tag(name="Poll")
 */
#[Route('/poll')]
final class PollActionsController extends AbstractController
{
    private const MAX_OPTION = 25;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PollService $pollService,
        private LockFactory $lockFactory,
    ) {}

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create-admin-poll', methods: ['POST'])]
    public function createPollAdmin(Request $request): JsonResponse
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

        return $this->json($poll, Response::HTTP_OK);
    }

    #[Route('/poll/{poll}', methods: ['GET'])]
    public function getPoll(Poll $poll): JsonResponse
    {
        return $this->json($poll, Response::HTTP_OK);
    }

    #[Route('/vote/{poll}', methods: ['POST'])]
    public function vote(Poll $poll, Request $request): JsonResponse
    {
        $lock = $this->lockFactory->createLock('poll_' . $poll->getId());

        if ($lock->acquire()) {
            /** @var User $user */
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

        return $this->json($poll, Response::HTTP_OK, [], ['user' => $this->getUser()]);
    }

    #[Route('/remove-votes/{poll}', methods: ['POST'])]
    public function removeVotes(Poll $poll, Request $request): JsonResponse
    {
        $lock = $this->lockFactory->createLock('poll_' . $poll->getId());

        if ($lock->acquire()) {
            /** @var User $user */
            $user = $this->getUser();

            $this->pollService->removeVotes($poll, $user);

            $lock->release();
        }

        return $this->json($poll, Response::HTTP_OK);
    }

    #[Route('/close-poll/{poll}', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function closePoll(Poll $poll, Request $request): JsonResponse
    {
        $lock = $this->lockFactory->createLock('poll_' . $poll->getId());

        if ($lock->acquire()) {
            $this->pollService->closePoll($poll);

            $lock->release();
        }

        return $this->json($poll, Response::HTTP_OK);
    }
}
