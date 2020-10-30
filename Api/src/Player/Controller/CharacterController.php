<?php

namespace Mush\Player\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Mush\Game\Enum\CharacterEnum;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use OpenApi\Annotations as OA;

/**
 * Class UsersController
 * @package Mush\Controller
 * @Route(path="/character")
 */
class CharacterController extends AbstractFOSRestController
{
    private TranslatorInterface $translator;

    /**
     * CharacterController constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Display the list of characters with their description
     * @OA\Tag(name="Character")
     * @Security(name="Bearer")
     * @Rest\Get(path="")
     */
    public function getCharactersAction(): Response
    {
        $characters = [];
        foreach (CharacterEnum::getAll() as $characterName) {
            $characters[] = [
                'fullName' => $this->translator->trans("{$characterName}.fullname", [], 'characters'),
                'employment' => $this->translator->trans("{$characterName}.employment", [], 'characters'),
                'abstract' => $this->translator->trans("{$characterName}.abstract", [], 'characters'),
                'biography' => $this->translator->trans("{$characterName}.biography", [], 'characters'),
            ];
        }

        $view = $this->view($characters, 200);

        return $this->handleView($view);
    }
}
