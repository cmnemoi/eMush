<?php

namespace Mush\Action\Controller;

use Mush\Action\Service\ActionServiceInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UsersController
 * @package Mush\Controller
 * @Route(path="/action")
 */
class ActionController extends AbstractFOSRestController
{
    private ActionServiceInterface $actionService;

    /**
     * ActionController constructor.
     * @param ActionServiceInterface $actionService
     */
    public function __construct(ActionServiceInterface $actionService)
    {
        $this->actionService = $actionService;
    }

    /**
     * @Rest\Post(path="")
     */
    public function createAction(Request $request): Response
    {
        try {
            $result = $this->actionService->executeAction($this->getUser()->getCurrentGame(), $request->get('action'), $request->get('params'));
        } catch (\InvalidArgumentException $exception) {
            return $this->handleView($this->view(['error' => $exception->getMessage()], 422));
        }

        $view = $this->view($result, 200);

        return $this->handleView($view);
    }
}