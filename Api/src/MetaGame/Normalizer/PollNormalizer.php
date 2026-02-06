<?php

namespace Mush\MetaGame\Normalizer;

use ApiPlatform\Api\IriConverterInterface;
use Mush\MetaGame\Entity\Poll\Poll;
use Mush\User\Entity\User;
use Mush\User\Service\UserServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PollNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private UserServiceInterface $userService,
        private readonly TokenStorageInterface $tokenStorage,
        private IriConverterInterface $iriConverter
    ) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Poll;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Poll::class => true,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Poll $poll */
        $poll = $object;

        /** @var ?User $user */
        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user === null) {
            if (isset($context['user'])) {
                $user = $context['user'];
            } else {
                return [];
            }
        }

        $createdAt = $poll->getCreatedAt();
        if ($createdAt === null) {
            throw new \LogicException('Should have a creation date');
        }

        $userCanVote = $poll->isImportant() ? $this->userService->userCanVoteInImportantPoll($user, $createdAt) && $poll->canUserVote($user) : $poll->canUserVote($user);

        return [
            '@id' => $this->iriConverter->getIriFromResource($poll),
            'id' => $poll->getId(),
            'title' => $poll->getTitle(),
            'voteCount' => $poll->getVoteCount(),
            'canVote' => $userCanVote,
            'remainingVotes' => $poll->getRemainingsVotes($user),
            'voted' => $poll->getUserVotes($user)->count() > 0,
            'options' => $this->normalizeOptions($poll, $user),
            'isClosed' => $poll->isClosed(),
        ];
    }

    private function normalizeOptions(Poll $poll, User $user): array
    {
        $normalizedOptions = [];

        foreach ($poll->getOptions() as $option) {
            if ($option !== null) {
                $normalizedOptions[] = [
                    'id' => $option->getId(),
                    'name' => $option->getName(),
                    'votes' => $option->getVotes()->count(),
                    'voted' => $option->userAlreadyVoted($user),
                ];
            }
        }

        return $normalizedOptions;
    }
}
