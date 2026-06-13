<?php

declare(strict_types=1);

namespace Mush\Daedalus\Controller;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Dto\DaedalusCreateRequest;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Daedalus\Service\DaedalusWidgetServiceInterface;
use Mush\Exploration\Service\CreateAPlanetInOrbitServiceInterface;
use Mush\Game\Controller\AbstractGameController;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Normalizer\SelectableCharacterNormalizer;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\User\Entity\User;
use Mush\User\Voter\UserVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/daedaluses')]
class DaedalusController extends AbstractGameController
{
    private const int MAX_CHARACTERS_TO_RETURN = 4;

    public function __construct(
        AdminServiceInterface $adminService,
        private DaedalusServiceInterface $daedalusService,
        private DaedalusWidgetServiceInterface $daedalusWidgetService,
        private TranslationServiceInterface $translationService,
        private PlayerInfoRepository $playerInfoRepository,
        private ValidatorInterface $validator,
        private RandomServiceInterface $randomService,
        private GameConfigServiceInterface $gameConfigService,
        private CycleServiceInterface $cycleService,
        private SelectableCharacterNormalizer $selectableCharacterNormalizer,
        private CreateAPlanetInOrbitServiceInterface $createAPlanetInOrbitService,
    ) {
        parent::__construct($adminService);
    }

    /**
     * Display available daedalus and characters.
     */
    #[Route('/available-characters', methods: ['GET'])]
    public function getAvailableCharacter(Request $request): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();

        $this->denyAccessUnlessGranted(UserVoter::NOT_IN_GAME, message: 'You are already in game!');
        $this->denyAccessUnlessGranted(UserVoter::HAS_ACCEPTED_RULES, message: 'You must accept the rules to play!');
        $this->denyAccessUnlessGranted(UserVoter::IS_NOT_BANNED, message: 'You have been banned!');

        $language = $request->query->getString('language');

        $gameConfig = $this->gameConfigService->getConfigByName(GameConfigEnum::DEFAULT);
        $daedalus = $this->daedalusService->findOrCreateAvailableDaedalus($language, $user, $gameConfig);

        $availableCharacters = $this->daedalusService->findAvailableCharacterForDaedalus($daedalus);

        $nbCharactersToReturn = min(self::MAX_CHARACTERS_TO_RETURN, \count($availableCharacters));

        $availableCharacters = $this->randomService->getRandomElements($availableCharacters->toArray(), $nbCharactersToReturn);
        $characters = [];

        /** @var CharacterConfig $character */
        foreach ($availableCharacters as $character) {
            $characters[] = $this->selectableCharacterNormalizer->normalize(
                $character,
                context: [
                    'character' => $character,
                    'daedalus' => $daedalus,
                ]
            );
        }

        return $this->json(['daedalus' => $daedalus->getId(), 'characters' => $characters], 200);
    }

    /**
     * Display daedalus minimap.
     */
    #[Route('/{id}/minimap', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDaedalusMinimapsAction(Daedalus $daedalus): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        $playerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user);

        if (!$playerInfo) {
            throw $this->createAccessDeniedException('User should be in game');
        }

        return $this->json($this->daedalusWidgetService->getMinimap($daedalus, $playerInfo->getPlayerOrThrow()), 200);
    }

    /**
     * Create a Daedalus.
     */
    #[Route('/create-daedalus', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function createDaedalus(DaedalusCreateRequest $daedalusCreateRequest): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        if (\count($violations = $this->validator->validate($daedalusCreateRequest))) {
            return $this->json($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->denyUnlessUserAdmin($user);

        /** @var GameConfig $gameConfig */
        $gameConfig = $daedalusCreateRequest->getConfig();

        $this->daedalusService->createDaedalus(
            $gameConfig,
            $daedalusCreateRequest->getName() ?? '',
            $daedalusCreateRequest->getLanguage() ?? ''
        );

        return $this->json(null, 200);
    }

    /**
     * Destroy the specified Daedalus.
     */
    #[Route('/destroy-daedalus/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function destroyDaedalus(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->denyUnlessUserAdmin($user);

        $daedalusId = $request->query->getInt('id');

        /** @var Daedalus $daedalus */
        $daedalus = $this->daedalusService->findById($daedalusId);
        if ($daedalus === null) {
            return $this->json(['error' => 'Daedalus not found'], 404);
        }
        if ($daedalus->getDaedalusInfo()->isDaedalusFinished()) {
            return $this->json(['error' => 'Daedalus is already finished'], 400);
        }
        if ($daedalus->isDaedalusOrExplorationChangingCycle()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }

        $this->daedalusService->endDaedalus(
            $daedalus,
            EndCauseEnum::SUPER_NOVA,
            new \DateTime()
        );

        return $this->json(null, 200);
    }

    /**
     * Destroy all non finished Daedaluses.
     */
    #[Route('/destroy-all-daedaluses', methods: ['POST'])]
    public function destroyAllDaedaluses(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->denyUnlessUserAdmin($user);

        $daedaluses = $this->daedalusService->findAllNonFinishedDaedaluses();
        if (\count($daedaluses) === 0) {
            return $this->json(['error' => 'No daedaluses found'], 404);
        }

        foreach ($daedaluses as $daedalus) {
            $this->daedalusService->endDaedalus(
                $daedalus,
                EndCauseEnum::SUPER_NOVA,
                new \DateTime()
            );
        }

        return $this->json(null, 200);
    }

    /**
     * Travel instantly to a newly created planet.
     */
    #[Route('/create-planet/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function createPlanet(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->denyUnlessUserAdmin($user);

        $daedalusId = (int) $request->attributes->get('id');

        /** @var Daedalus $daedalus */
        $daedalus = $this->daedalusService->findById($daedalusId);
        if ($daedalus === null) {
            return $this->json(['error' => 'Daedalus not found'], 404);
        }
        if ($daedalus->getDaedalusInfo()->isDaedalusFinished()) {
            return $this->json(['error' => 'Daedalus is finished'], 400);
        }
        if ($daedalus->isDaedalusOrExplorationChangingCycle()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(new \DateTime(), $daedalus);

        $this->createAPlanetInOrbitService->execute(daedalus: $daedalus, revealAllSectors: true);

        return $this->json(null, 200);
    }
}
