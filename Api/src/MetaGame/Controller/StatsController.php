<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\MetaGame\Service\StatsServiceInterface;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stats')]
final class StatsController extends AbstractController
{
    public function __construct(
        private readonly StatsServiceInterface $statsService,
        private readonly PlayerRepositoryInterface $playerRepository,
    ) {}

    #[IsGranted('ROLE_MODERATOR')]
    #[Route('/skills', methods: ['POST'])]
    public function getSkillCount(Request $request): JsonResponse
    {
        $skillName = $request->getPayload()->get('skillName');
        $result = $this->statsService->getPlayerSkillCount(SkillEnum::from((string) $skillName));

        return $this->json($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Route('/skills/list', methods: ['POST'])]
    public function getSkillList(): JsonResponse
    {
        $result = $this->statsService->getSkillList();

        return $this->json($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Route('/characters/list', methods: ['POST'])]
    public function getCharacterList(): JsonResponse
    {
        $result = $this->statsService->getCharacterList();

        return $this->json($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Route('/skills/all', methods: ['POST'])]
    public function getAllSkillCount(): JsonResponse
    {
        $result = $this->statsService->getAllSkillCount();

        return $this->json($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Route('/skills/characters', methods: ['POST'])]
    public function getSkillByCharacter(Request $request): JsonResponse
    {
        $result = $this->statsService->getSkillByCharacter((string) $request->getPayload()->get('characterName'));

        return $this->json($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Route('/explorations/fights', methods: ['POST'])]
    public function getExploFightData(Request $request): JsonResponse
    {
        $result = $this->statsService->getExploFightData((int) $request->getPayload()->get('daedalusId', 0));

        return $this->json($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Route('/mush', methods: ['POST'])]
    public function getMushtData(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->isInGame()) {
            $result = "Can't request this data if in game.";
        } else {
            $result = $this->statsService->getMushData((int) $request->getPayload()->get('first', 0), (int) $request->getPayload()->get('last', 0));
        }

        return $this->json($result, Response::HTTP_OK);
    }
}
