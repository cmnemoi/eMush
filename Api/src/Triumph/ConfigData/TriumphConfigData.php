<?php

declare(strict_types=1);

namespace Mush\Triumph\ConfigData;

use Mush\Action\Enum\ActionEnum;
use Mush\Communications\Event\LinkWithSolEstablishedEvent;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectRequirementName;
use Mush\Project\Enum\ProjectType;
use Mush\Project\Event\ProjectEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Triumph\Dto\TriumphConfigDto;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Enum\TriumphTarget;
use Mush\Triumph\Event\TriumphSourceEventInterface;

abstract class TriumphConfigData
{
    /**
     * @return array<TriumphConfigDto>
     */
    public static function getAll(): array
    {
        return [
            new TriumphConfigDto(
                key: TriumphEnum::CYCLE_HUMAN->toConfigKey('default'),
                name: TriumphEnum::CYCLE_HUMAN,
                targetedEvent: DaedalusCycleEvent::DAEDALUS_NEW_CYCLE,
                scope: TriumphScope::ALL_ACTIVE_HUMANS,
                quantity: 1,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::CYCLE_MUSH->toConfigKey('default'),
                name: TriumphEnum::CYCLE_MUSH,
                targetedEvent: DaedalusCycleEvent::DAEDALUS_NEW_CYCLE,
                scope: TriumphScope::ALL_MUSHS,
                quantity: -2,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::CHUN_LIVES->toConfigKey('default'),
                name: TriumphEnum::CHUN_LIVES,
                targetedEvent: DaedalusCycleEvent::DAEDALUS_NEW_CYCLE,
                tagConstraints: [
                    EventEnum::NEW_DAY => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                target: CharacterEnum::CHUN,
                quantity: 1,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::RETURN_TO_SOL->toConfigKey('default'),
                name: TriumphEnum::RETURN_TO_SOL,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    ActionEnum::RETURN_TO_SOL->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                quantity: 20,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::SOL_MUSH_INTRUDER->toConfigKey('default'),
                name: TriumphEnum::SOL_MUSH_INTRUDER,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    ActionEnum::RETURN_TO_SOL->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                quantity: -10,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::SOL_MUSH_INVASION->toConfigKey('default'),
                name: TriumphEnum::SOL_MUSH_INVASION,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    ActionEnum::RETURN_TO_SOL->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_MUSHS,
                quantity: 16,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::MUSH_VICTORY->toConfigKey('default'),
                name: TriumphEnum::MUSH_VICTORY,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    EndCauseEnum::KILLED_BY_NERON => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_MUSHS,
                quantity: 8,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::MUSH_INITIAL_BONUS->toConfigKey('default'),
                name: TriumphEnum::MUSH_INITIAL_BONUS,
                targetedEvent: DaedalusEvent::FULL_DAEDALUS,
                scope: TriumphScope::ALL_ALIVE_MUSHS,
                quantity: 120,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::EXPEDITION->toConfigKey('default'),
                name: TriumphEnum::EXPEDITION,
                targetedEvent: ExplorationEvent::EXPLORATION_STARTED,
                scope: TriumphScope::ALL_ACTIVE_EXPLORERS,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::EXPLORATOR->toConfigKey('default'),
                name: TriumphEnum::EXPLORATOR,
                targetedEvent: ExplorationEvent::EXPLORATION_STARTED,
                scope: TriumphScope::ALL_ACTIVE_HUMAN_EXPLORERS,
                target: CharacterEnum::HUA,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::SOL_CONTACT->toConfigKey('default'),
                name: TriumphEnum::SOL_CONTACT,
                targetedEvent: LinkWithSolEstablishedEvent::class,
                tagConstraints: [
                    LinkWithSolEstablishedEvent::FIRST_CONTACT => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                quantity: 8
            ),
            new TriumphConfigDto(
                key: TriumphEnum::RESEARCH_SMALL->toConfigKey('default'),
                name: TriumphEnum::RESEARCH_SMALL,
                targetedEvent: ProjectEvent::PROJECT_FINISHED,
                tagConstraints: [
                    ProjectName::MUSHOVORE_BACTERIA->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectName::PATULINE_SCRAMBLER->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectName::MERIDON_SCRAMBLER->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectName::CREATE_MYCOSCAN->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectName::ANTISPORE_GAS->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectName::MYCOALARM->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectName::PHEROMODEM->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectName::MUSHICIDE_SOAP->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectName::CONSTIPASPORE_SERUM->toString() => TriumphSourceEventInterface::ANY_TAG,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::RESEARCH_STANDARD->toConfigKey('default'),
                name: TriumphEnum::RESEARCH_STANDARD,
                targetedEvent: ProjectEvent::PROJECT_FINISHED,
                tagConstraints: [
                    ProjectName::MUSH_LANGUAGE->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectName::MUSH_HUNTER_ZC16H->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectName::MUSH_RACES->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectName::MUSH_REPRODUCTIVE_SYSTEM->toString() => TriumphSourceEventInterface::ANY_TAG,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                quantity: 6,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::RESEARCH_BRILLANT->toConfigKey('default'),
                name: TriumphEnum::RESEARCH_BRILLANT,
                targetedEvent: ProjectEvent::PROJECT_FINISHED,
                tagConstraints: [
                    ProjectName::RETRO_FUNGAL_SERUM->toString() => TriumphSourceEventInterface::ANY_TAG,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                quantity: 16,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::MUSH_SPECIALIST->toConfigKey('default'),
                name: TriumphEnum::MUSH_SPECIALIST,
                targetedEvent: ProjectEvent::PROJECT_FINISHED,
                tagConstraints: [
                    ProjectRequirementName::MUSH_SAMPLE_IN_LABORATORY->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectRequirementName::MUSH_PLAYER_DEAD->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectRequirementName::MUSH_GENOME_DISK_IN_LABORATORY->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ProjectRequirementName::CHUN_IN_LABORATORY->toString() => TriumphSourceEventInterface::ANY_TAG,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                target: CharacterEnum::FINOLA,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::PRECIOUS_BODY->toConfigKey('default'),
                name: TriumphEnum::PRECIOUS_BODY,
                targetedEvent: ProjectEvent::PROJECT_FINISHED,
                tagConstraints: [
                    ProjectRequirementName::CHUN_IN_LABORATORY->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                target: CharacterEnum::CHUN,
                quantity: 4,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::MAGELLAN_ARK->toConfigKey('default'),
                name: TriumphEnum::MAGELLAN_ARK,
                targetedEvent: ProjectEvent::PROJECT_FINISHED,
                tagConstraints: [
                    ProjectType::NERON_PROJECT->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                target: CharacterEnum::KUAN_TI,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::PILGRED_MOTHER->toConfigKey('default'),
                name: TriumphEnum::PILGRED_MOTHER,
                targetedEvent: ProjectEvent::PROJECT_ADVANCED,
                tagConstraints: [
                    ProjectName::PILGRED->toString() => TriumphSourceEventInterface::ALL_TAGS,
                    ProjectEvent::NEXT_20_PERCENTS => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                target: CharacterEnum::RALUCA,
                quantity: 2,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::AMBITIOUS->toConfigKey('default'),
                name: TriumphEnum::AMBITIOUS,
                targetedEvent: StatusEvent::STATUS_APPLIED,
                tagConstraints: [
                    CharacterEnum::STEPHEN => TriumphSourceEventInterface::ALL_TAGS,
                    PlayerStatusEnum::HAS_GAINED_COMMANDER_TITLE => TriumphSourceEventInterface::ANY_TAG,
                    PlayerStatusEnum::HAS_GAINED_COM_MANAGER_TITLE => TriumphSourceEventInterface::ANY_TAG,
                    PlayerStatusEnum::HAS_GAINED_NERON_MANAGER_TITLE => TriumphSourceEventInterface::ANY_TAG,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                target: TriumphTarget::STATUS_HOLDER->toString(),
                quantity: 4,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::KUBE_SOLVED->toConfigKey('default'),
                name: TriumphEnum::KUBE_SOLVED,
                targetedEvent: StatusEvent::STATUS_APPLIED,
                tagConstraints: [
                    PlayerStatusEnum::POINTLESS_PLAYER => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_PLAYERS,
                target: TriumphTarget::STATUS_HOLDER->toString(),
                quantity: 5,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::CHUN_DEAD->toConfigKey('default'),
                name: TriumphEnum::CHUN_DEAD,
                targetedEvent: PlayerEvent::DEATH_PLAYER,
                tagConstraints: [
                    CharacterEnum::CHUN => TriumphSourceEventInterface::ALL_TAGS,
                    EndCauseEnum::EDEN => TriumphSourceEventInterface::NONE_TAGS,
                    EndCauseEnum::QUARANTINE => TriumphSourceEventInterface::NONE_TAGS,
                    EndCauseEnum::SOL_RETURN => TriumphSourceEventInterface::NONE_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_MUSHS,
                quantity: 7,
            ),
        ];
    }

    public static function getByName(TriumphEnum $name): TriumphConfigDto
    {
        return current(
            array_filter(self::getAll(), static fn (TriumphConfigDto $dto) => $dto->name === $name)
        );
    }
}
