<?php

namespace Mush\Daedalus\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Daedalus\Service\DaedalusWidgetServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UsersController.
 *
 * @Route(path="/daedaluses")
 */
class DaedalusController extends AbstractFOSRestController
{
    private DaedalusServiceInterface $daedalusService;
    private DaedalusWidgetServiceInterface $daedalusWidgetService;
    private TranslationServiceInterface $translationService;
    private PlayerInfoRepository $playerInfoRepository;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        DaedalusWidgetServiceInterface $daedalusWidgetService,
        TranslationServiceInterface $translationService,
        PlayerInfoRepository $playerInfoRepository,
    ) {
        $this->daedalusService = $daedalusService;
        $this->daedalusWidgetService = $daedalusWidgetService;
        $this->translationService = $translationService;
        $this->playerInfoRepository = $playerInfoRepository;
    }

    /**
     * Display available daedalus and characters.
     *
     * @OA\Tag (name="Daedalus")
     *
     * @Security (name="Bearer")
     *
     * @Rest\Get (path="/available-characters")
     */
    public function getAvailableCharacter(Request $request): View
    {
        $name = $request->get('name', '');

        $daedalus = $this->daedalusService->findAvailableDaedalus($name);

        if ($daedalus === null) {
            return $this->view(['error' => 'Daedalus not found'], 404);
        }

        $availableCharacters = $this->daedalusService->findAvailableCharacterForDaedalus($daedalus);
        $characters = [];
        /** @var CharacterConfig $character */
        foreach ($availableCharacters as $character) {
            $characters[] = [
                'key' => $character->getCharacterName(),
                'name' => $this->translationService->translate(
                    $character->getCharacterName() . '.name',
                    [],
                    'characters',
                    $daedalus->getLanguage()
                ),
            ];
        }

        return $this->view(['daedalus' => $daedalus->getId(), 'characters' => $characters], 200);
    }

    /**
     * Display daedalus minimap.
     *
     * @OA\Tag (name="Daedalus")
     *
     * @Security (name="Bearer")
     *
     * @Rest\Get(path="/{id}/minimap", requirements={"id"="\d+"})
     */
    public function getDaedalusMinimapsAction(Daedalus $daedalus): View
    {
        /** @var User $user */
        $user = $this->getUser();
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);

        if (!$playerInfo) {
            throw new AccessDeniedException('User should be in game');
        }

        return $this->view($this->daedalusWidgetService->getMinimap($daedalus, $playerInfo->getPlayer()), 200);
    }
}
