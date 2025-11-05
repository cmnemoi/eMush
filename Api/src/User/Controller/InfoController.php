<?php

declare(strict_types=1);

namespace Mush\User\Controller;

use Composer\InstalledVersions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class InfoController extends AbstractController
{
    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/version', name: 'version', methods: ['GET'])]
    public function version(): JsonResponse
    {
        return new JsonResponse(['version' => InstalledVersions::getRootPackage()['pretty_version']]);
    }
}
