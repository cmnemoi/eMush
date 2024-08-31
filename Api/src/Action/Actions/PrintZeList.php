<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AllMushsAreDead;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasSkill;
use Mush\Action\Validator\HasStatus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PrintZeList extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::PRINT_ZE_LIST;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private GameEquipmentService $gameEquipmentService,
        private RandomServiceInterface $randomService,
        private StatusServiceInterface $statusService,
        private TranslationServiceInterface $translationService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasSkill([
                'skill' => SkillEnum::TRACKER,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => DaedalusStatusEnum::ZE_LIST_HAS_BEEN_PRINTED,
                'target' => HasStatus::DAEDALUS,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::LIST_ALREADY_PRINTED,
            ]),
            new AllMushsAreDead([
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::LIST_NO_MUSH,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->createZeList();
        $this->createHasPrintedZeListStatus();
    }

    private function createZeList(): void
    {
        $zeList = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::DOCUMENT,
            equipmentHolder: $this->player,
            reasons: $this->getTags(),
            time: new \DateTime(),
            visibility: VisibilityEnum::PUBLIC,
        );

        $this->addNamesToZeList($zeList);
    }

    private function createHasPrintedZeListStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::ZE_LIST_HAS_BEEN_PRINTED,
            holder: $this->player->getDaedalus(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function addNamesToZeList(GameEquipment $zeList): void
    {
        $this->statusService->createContentStatus(
            content: $this->translatedList(),
            holder: $zeList,
            tags: $this->getTags(),
        );
    }

    private function translatedList(): string
    {
        $selectedPlayers = $this->selectedPlayers();
        $lastPlayer = array_pop($selectedPlayers);

        $translatedFirstPlayers = array_map(
            fn (Player $player) => $this->translationService->translate(
                key: \sprintf('%s.name', $player->getLogName()),
                parameters: [],
                domain: 'characters',
                language: $this->player->getLanguage()
            ),
            $selectedPlayers,
        );
        $translatedLastPlayer = $this->translationService->translate(
            key: \sprintf('%s.name', $lastPlayer->getLogName()),
            parameters: [],
            domain: 'characters',
            language: $this->player->getLanguage()
        );

        return $this->translationService->translate(
            key: 'ze_list',
            parameters: [
                'firstPlayers' => implode(', ', array_map(static fn (string $name) => $name, $translatedFirstPlayers)),
                'lastPlayer' => $translatedLastPlayer,
                'quantity' => \count($this->selectedPlayers()),
            ],
            domain: 'event_log',
            language: $this->player->getLanguage(),
        );
    }

    private function selectedPlayers(): array
    {
        $players = $this->player->getDaedalus()->getPlayers()->toArray();
        $randomPlayers = $this->randomService->getRandomElements($players, $this->numberOfNames());

        $selectedPlayers = [$this->selectedAlphaMush(), ...$randomPlayers];
        shuffle($selectedPlayers);

        return $selectedPlayers;
    }

    private function selectedAlphaMush(): Player
    {
        $players = $this->player->getDaedalus()->getPlayers();
        $alphaMushs = $players->filter(static fn (Player $player) => $player->isAlphaMush());

        // Temporary condition during the alpha as on-going ships do not have alpha mushes
        // Can be removed safely when all ships started after September 1, 2024 are finished
        if ($alphaMushs->isEmpty()) {
            return $this->randomService->getRandomElement($players->getMushPlayer()->toArray());
        }

        return $this->randomService->getRandomElement($alphaMushs->toArray());
    }

    private function numberOfNames(): int
    {
        return max($this->getOutputQuantity() - $this->numberOfDaysElapsed() - 1, 0);
    }

    private function numberOfDaysElapsed(): int
    {
        $daedalus = $this->player->getDaedalus();

        /** @var \DateTime $createdAt */
        $createdAt = $daedalus->getCreatedAt();

        return $createdAt->diff(new \DateTime('now'))->days;
    }
}
