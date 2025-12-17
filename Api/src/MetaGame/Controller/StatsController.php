<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Mush\MetaGame\Service\StatsServiceInterface;
use Mush\Skill\Enum\SkillEnum;
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
    ) {}

    #[IsGranted('ROLE_ADMIN')]
    #[Post(path: '/skills')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getSkillCount(Request $request): View
    {
        $skillName = $request->get('skillName');
        $result = $this->statsService->getPlayerSkillCount(SkillEnum::from($skillName));

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Post(path: '/skills/list')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getSkillList(): View
    {
        $result = $this->statsService->getSkillList();

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Post(path: '/characters/list')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getCharacterList(): View
    {
        $result = $this->statsService->getCharacterList();

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Post(path: '/skills/all')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getAllSkillCount(): View
    {
        $result = $this->statsService->getAllSkillCount();

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Post(path: '/skills/characters')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getSkillByCharacter(Request $request): View
    {
        $result = $this->statsService->getSkillByCharacter($request->get('characterName'));

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Post(path: '/explorations/fights')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getExploFightData(Request $request): View
    {
        $result = $this->statsService->getExploFightData($request->get('daedalusId', 0));

        return $this->view($result, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Post(path: '/mush')]
    #[NelmioSecurity(name: 'Bearer')]
    public function getMushtData(Request $request): View
    {
        $result = $this->statsService->getMushData();

        return $this->view($result, Response::HTTP_OK);
    }
}
