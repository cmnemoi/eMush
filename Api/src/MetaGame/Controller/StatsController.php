<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Mush\MetaGame\Service\StatsServiceInterface;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Attribute\Security as NelmioSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class for actions that can be performed by admins.
 *
 * @Route(path="/stats")
 */
final class StatsController extends AbstractFOSRestController
{
    public function __construct(
        private readonly StatsServiceInterface $statsService,
        private readonly PlayerRepositoryInterface $playerRepository,
    ) {}

    #[IsGranted('ROLE_MODERATOR')]
    #[Post(path: '/skills')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getSkillCount(Request $request): View
    {
        $skillName = $request->get('skillName');
        $result = $this->statsService->getPlayerSkillCount(SkillEnum::from($skillName));

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Post(path: '/skills/list')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getSkillList(): View
    {
        $result = $this->statsService->getSkillList();

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Post(path: '/characters/list')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getCharacterList(): View
    {
        $result = $this->statsService->getCharacterList();

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Post(path: '/skills/all')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getAllSkillCount(): View
    {
        $result = $this->statsService->getAllSkillCount();

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Post(path: '/skills/characters')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getSkillByCharacter(Request $request): View
    {
        $result = $this->statsService->getSkillByCharacter($request->get('characterName'));

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Post(path: '/explorations/fights')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getExploFightData(Request $request): View
    {
        $result = $this->statsService->getExploFightData($request->get('daedalusId', 0));

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MODERATOR')]
    #[Post(path: '/mush')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getMushtData(Request $request): View
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->isInGame()) {
            $result = "Can't request this data if in game.";
        } else {
            $result = $this->statsService->getMushData();
        }

        return $this->view($result, Response::HTTP_OK);
    }
}
