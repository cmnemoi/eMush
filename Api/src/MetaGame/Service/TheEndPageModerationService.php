<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;

final class TheEndPageModerationService implements TheEndPageModerationServiceInterface
{   
    private EntityManagerInterface $entityManager;
    private TranslationServiceInterface $translationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslationServiceInterface $translationService
    ) {
        $this->entityManager = $entityManager;
        $this->translationService = $translationService;
    }

    public function editEndMessage(ClosedPlayer $closedPlayer): void
    {
        $message = $this->translationService->translate(
            key: 'edited_by_neron',
            parameters: [],
            domain: 'moderation',
            language: $closedPlayer->getClosedDaedalus()->getLanguage(),
        );

        $closedPlayer->setMessage($message);
        $this->entityManager->persist($closedPlayer);
        $this->entityManager->flush();
    }

    public function hideEndMessage(ClosedPlayer $closedPlayer): void
    {
        $closedPlayer->hideMessage();
        $this->entityManager->persist($closedPlayer);
        $this->entityManager->flush();
    }
}
