<?php

namespace Mush\Daedalus\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UsersController.
 *
 * @Route(path="/daedalus")
 */
class DaedalusController extends AbstractFOSRestController
{
    private DaedalusServiceInterface $daedalusService;
    private GameConfigServiceInterface $gameConfigService;
    private TranslatorInterface $translator;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        GameConfigServiceInterface $gameConfigService,
        TranslatorInterface $translator
    ) {
        $this->daedalusService = $daedalusService;
        $this->gameConfigService = $gameConfigService;
        $this->translator = $translator;
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
     * @OA\Tag(name="Daedalus")
     * @Security(name="Bearer")
     * @Rest\Get(path="/available-characters")
     */
    public function getAvailableCharacter()
    {
        $daedalus = $this->daedalusService->findAvailableDaedalus();

        if ($daedalus === null) {
            throw new NotFoundHttpException();
        }

        $currentCharacters = array_map(fn (Player $player) => ($player->getPerson()), $daedalus->getPlayers()->toArray());

        $availableCharacters = array_filter(CharacterEnum::getAll(), fn ($character) => (!in_array($character, $currentCharacters)));

        $characters = [];
        foreach ($availableCharacters as $character) {
            $characters[] = [
                'key' => $character,
                'name' => $this->translator->trans($character . '.name', [], 'characters'),
            ];
        }

        return $this->view(['daedalus' => $daedalus->getId(), 'characters' => $characters], 200);
    }
}
