<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Entity\SkillConfigCollection;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\SkillConfigRepositoryInterface;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UseResetVaccine extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::USE_RESET_VACCINE;

    public function __construct(
        protected EventServiceInterface $eventService,
        protected ActionServiceInterface $actionService,
        protected ValidatorInterface $validator,
        private DeletePlayerSkillService $deletePlayerSkill,
        private readonly SkillConfigRepositoryInterface $skillConfigRepository,
        private RandomServiceInterface $randomService
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void {}

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->destroyVaccine();

        $this->deleteAllHumanSkillsFromPlayer();

        $this->createNewRandomSetOfSkillsForPlayer();
    }

    private function destroyVaccine(): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            equipment: $this->gameEquipmentTarget(),
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, $equipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function deleteAllHumanSkillsFromPlayer(): void
    {
        foreach ($this->player->getAvailableHumanSkills() as $skill) {
            $this->player->removeFromAvailableHumanSkills($skill);

            if ($this->player->hasSkill($skill->getName())) {
                $this->deletePlayerSkill->execute($skill->getName(), player: $this->player);
            }
        }
    }

    private function createNewRandomSetOfSkillsForPlayer(): void
    {
        // This function has to juggle back and forth between String and SkillEnum so much :sob:

        $importantSkills = new ProbaCollection([
            SkillEnum::APPRENTICE->value => 1,
            SkillEnum::ASTROPHYSICIST->value => 5,
            SkillEnum::BIOLOGIST->value => 5,
            SkillEnum::BOTANIST->value => 5,
            SkillEnum::DIPLOMAT->value => 3,
            SkillEnum::GUNNER->value => 3,
            SkillEnum::IT_EXPERT->value => 5,
            SkillEnum::MEDIC->value => 2,
            SkillEnum::PILOT->value => 5,
            SkillEnum::ROBOTICS_EXPERT->value => 2,
            SkillEnum::SHOOTER->value => 5,
            SkillEnum::SHRINK->value => 5,
            SkillEnum::TECHNICIAN->value => 5,
            SkillEnum::SOLID->value => 3,
        ]);

        $selectedSkillNames = $this->randomService->getRandomElementsFromProbaCollection($importantSkills, 1);

        $selectedSkills = [];

        foreach ($selectedSkillNames as $skill) {
            $selectedSkills[] = SkillEnum::from($skill);
        }

        $filteredSkillsAndNonSkillEntries = [
            SkillEnum::NULL,
            SkillEnum::DISABLED_SPRINTER,
            SkillEnum::MANKIND_ONLY_HOPE, // following perks are all filtered on the grounds of "I don't like them being in the pool". Sue me
            SkillEnum::CRAZY_EYE,
            SkillEnum::MYCOLOGIST,
            SkillEnum::GENIUS,
            SkillEnum::PARANOID,
            SkillEnum::GREEN_THUMB,
        ];

        $otherPossibleSkills = array_filter(
            SkillEnum::cases(),
            static fn (SkillEnum $skill) => !\in_array($skill, [...SkillEnum::getMushSkills()->toArray(), ...$selectedSkills, ...$filteredSkillsAndNonSkillEntries], true)
        );

        $selectedSkills = [...$selectedSkills, ...$this->randomService->getRandomElements($otherPossibleSkills, 5 + (int) $this->player->hasStatus(PlayerStatusEnum::HAS_READ_MAGE_BOOK))];

        $skillConfigs = [];
        foreach ($selectedSkills as $skill) {
            $skillConfigs[] = $this->skillConfigRepository->findOneByNameAndDaedalusOrThrow($skill, $this->player->getDaedalus());
        }

        $this->player->setAvailableHumanSkills(new SkillConfigCollection($skillConfigs));
    }
}
