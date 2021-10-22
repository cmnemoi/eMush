<?php

namespace Mush\Daedalus\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Daedalus\Service\DaedalusWidgetServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UsersController.
 *
 * @Route(path="/daedalus")
 */
class DaedalusController extends AbstractFOSRestController
{
    private DaedalusServiceInterface $daedalusService;
    private DaedalusWidgetServiceInterface $daedalusWidgetService;
    private GameConfigServiceInterface $gameConfigService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        DaedalusWidgetServiceInterface $daedalusWidgetService,
        GameConfigServiceInterface $gameConfigService,
        TranslationServiceInterface $translationService
    ) {
        $this->daedalusService = $daedalusService;
        $this->daedalusWidgetService = $daedalusWidgetService;
        $this->gameConfigService = $gameConfigService;
        $this->translationService = $translationService;
    }

    /**
     * Display Daedalus informations.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The daedalus id",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Daedalus")
     * @Security(name="Bearer")
     * @Rest\Get(path="/{id}", requirements={"id"="\d+"})
     */
    public function getDaedalusAction(?Daedalus $daedalus): Response
    {
        if ($daedalus === null) {
            throw new NotFoundHttpException();
        }

        $view = $this->view($daedalus, 200);

        return $this->handleView($view);
    }

    /**
     * Create a Daedalus.
     *
     * @OA\Tag(name="Daedalus")
     * @Security(name="Bearer")
     * @Rest\Post(path="")
     */
    public function createDaedalusAction(): Response
    {
        $daedalus = $this->daedalusService->createDaedalus($this->gameConfigService->getConfig());

        $view = $this->view($daedalus, 201);

        return $this->handleView($view);
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
    public function getAvailableCharacter(): View
    {
        $daedalus = $this->daedalusService->findAvailableDaedalus();

        if ($daedalus === null) {
            $daedalus = $this->daedalusService->createDaedalus($this->gameConfigService->getConfig());
        }

        $availableCharacters = $this->daedalusService->findAvailableCharacterForDaedalus($daedalus);
        $characters = [];
        /** @var CharacterConfig $character */
        foreach ($availableCharacters as $character) {
            $characters[] = [
                'key' => $character->getName(),
                'name' => $this->translationService->translate($character->getName() . '.name', [], 'characters'),
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
        return $this->view($this->daedalusWidgetService->getMinimap($daedalus), 200);
    }
}
