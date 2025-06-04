<?php

declare(strict_types=1);

namespace Mush\Triumph\ConfigData;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Event\LinkWithSolEstablishedEvent;
use Mush\Communications\Event\RebelBaseDecodedEvent;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectRequirementName;
use Mush\Project\Enum\ProjectType;
use Mush\Project\Event\ProjectEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
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
                scope: TriumphScope::PERSONAL_CHUN,
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
                key: TriumphEnum::SUPER_NOVA->toConfigKey('default'),
                name: TriumphEnum::SUPER_NOVA,
                targetedEvent: PlayerEvent::DEATH_PLAYER,
                tagConstraints: [
                    EndCauseEnum::SUPER_NOVA => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_PLAYERS,
                targetSetting: TriumphTarget::EVENT_SUBJECT,
                quantity: 20,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::MUSH_INITIAL_BONUS->toConfigKey('default'),
                name: TriumphEnum::MUSH_INITIAL_BONUS,
                targetedEvent: PlayerEvent::CONVERSION_PLAYER,
                tagConstraints: [
                    DaedalusEvent::FULL_DAEDALUS => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_MUSHS,
                targetSetting: TriumphTarget::EVENT_SUBJECT,
                quantity: 120,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::EXPEDITION->toConfigKey('default'),
                name: TriumphEnum::EXPEDITION,
                targetedEvent: ExplorationEvent::EXPLORATION_STARTED,
                scope: TriumphScope::ALL_ALIVE_PLAYERS,
                targetSetting: TriumphTarget::ACTIVE_EXPLORERS,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::EXPLORATOR->toConfigKey('default'),
                name: TriumphEnum::EXPLORATOR,
                targetedEvent: ExplorationEvent::EXPLORATION_STARTED,
                scope: TriumphScope::PERSONAL_HUA,
                targetSetting: TriumphTarget::ACTIVE_EXPLORERS,
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
                scope: TriumphScope::PERSONAL_FINOLA,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::PRECIOUS_BODY->toConfigKey('default'),
                name: TriumphEnum::PRECIOUS_BODY,
                targetedEvent: ProjectEvent::PROJECT_FINISHED,
                tagConstraints: [
                    ProjectRequirementName::CHUN_IN_LABORATORY->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::PERSONAL_CHUN,
                quantity: 4,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::MAGELLAN_ARK->toConfigKey('default'),
                name: TriumphEnum::MAGELLAN_ARK,
                targetedEvent: ProjectEvent::PROJECT_FINISHED,
                tagConstraints: [
                    ProjectType::NERON_PROJECT->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::PERSONAL_KUAN_TI,
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
                scope: TriumphScope::PERSONAL_RALUCA,
                quantity: 2,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::AMBITIOUS->toConfigKey('default'),
                name: TriumphEnum::AMBITIOUS,
                targetedEvent: StatusEvent::STATUS_APPLIED,
                tagConstraints: [
                    PlayerStatusEnum::HAS_GAINED_COMMANDER_TITLE => TriumphSourceEventInterface::ANY_TAG,
                    PlayerStatusEnum::HAS_GAINED_COM_MANAGER_TITLE => TriumphSourceEventInterface::ANY_TAG,
                    PlayerStatusEnum::HAS_GAINED_NERON_MANAGER_TITLE => TriumphSourceEventInterface::ANY_TAG,
                ],
                scope: TriumphScope::PERSONAL_STEPHEN,
                targetSetting: TriumphTarget::EVENT_SUBJECT,
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
                targetSetting: TriumphTarget::EVENT_SUBJECT,
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
            new TriumphConfigDto(
                key: TriumphEnum::MUSH_FEAR->toConfigKey('default'),
                name: TriumphEnum::MUSH_FEAR,
                targetedEvent: PlayerEvent::DEATH_PLAYER,
                tagConstraints: [
                    PlayerStatusEnum::MUSH => TriumphSourceEventInterface::ALL_TAGS,
                    EndCauseEnum::EDEN => TriumphSourceEventInterface::NONE_TAGS,
                    EndCauseEnum::QUARANTINE => TriumphSourceEventInterface::NONE_TAGS,
                    EndCauseEnum::SOL_RETURN => TriumphSourceEventInterface::NONE_TAGS,
                ],
                scope: TriumphScope::PERSONAL_GIOELE,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::MUSHICIDE->toConfigKey('default'),
                name: TriumphEnum::MUSHICIDE,
                targetedEvent: PlayerEvent::DEATH_PLAYER,
                tagConstraints: [
                    EndCauseEnum::ASSASSINATED => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::BEHEADED => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::BLED => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::INJURY => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::ROCKETED => TriumphSourceEventInterface::ANY_TAG,
                    PlayerStatusEnum::MUSH => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                targetSetting: TriumphTarget::AUTHOR,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::MUSHICIDE_CAT->toConfigKey('default'),
                name: TriumphEnum::MUSHICIDE_CAT,
                targetedEvent: EquipmentEvent::EQUIPMENT_DESTROYED,
                tagConstraints: [
                    ActionEnum::SHOOT_CAT->value => TriumphSourceEventInterface::ALL_TAGS,
                    EquipmentStatusEnum::CAT_INFECTED => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                targetSetting: TriumphTarget::AUTHOR,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::HUMANOCIDE->toConfigKey('default'),
                name: TriumphEnum::HUMANOCIDE,
                targetedEvent: PlayerEvent::DEATH_PLAYER,
                tagConstraints: [
                    EndCauseEnum::ASSASSINATED => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::BEHEADED => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::BLED => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::INJURY => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::ROCKETED => TriumphSourceEventInterface::ANY_TAG,
                    PlayerStatusEnum::MUSH => TriumphSourceEventInterface::NONE_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_MUSHS,
                targetSetting: TriumphTarget::AUTHOR,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::HUMANOCIDE_CAT->toConfigKey('default'),
                name: TriumphEnum::HUMANOCIDE_CAT,
                targetedEvent: EquipmentEvent::EQUIPMENT_DESTROYED,
                tagConstraints: [
                    ActionEnum::SHOOT_CAT->value => TriumphSourceEventInterface::ALL_TAGS,
                    EquipmentStatusEnum::CAT_INFECTED => TriumphSourceEventInterface::NONE_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_MUSHS,
                targetSetting: TriumphTarget::AUTHOR,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::PSYCHOPAT->toConfigKey('default'),
                name: TriumphEnum::PSYCHOPAT,
                targetedEvent: PlayerEvent::DEATH_PLAYER,
                tagConstraints: [
                    EndCauseEnum::ASSASSINATED => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::BEHEADED => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::BLED => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::INJURY => TriumphSourceEventInterface::ANY_TAG,
                    EndCauseEnum::ROCKETED => TriumphSourceEventInterface::ANY_TAG,
                ],
                scope: TriumphScope::PERSONAL_CHAO,
                targetSetting: TriumphTarget::AUTHOR,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::PSYCHOCAT->toConfigKey('default'),
                name: TriumphEnum::PSYCHOCAT,
                targetedEvent: EquipmentEvent::EQUIPMENT_DESTROYED,
                tagConstraints: [
                    ActionEnum::SHOOT_CAT->value => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::PERSONAL_CHAO,
                targetSetting: TriumphTarget::AUTHOR,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::ALIEN_SCIENCE->toConfigKey('default'),
                name: TriumphEnum::ALIEN_SCIENCE,
                targetedEvent: PlayerEvent::DEATH_PLAYER,
                tagConstraints: [
                    EndCauseEnum::ALIEN_ABDUCTED => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_PLAYERS,
                targetSetting: TriumphTarget::EVENT_SUBJECT,
                quantity: 16,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::EDEN_AT_LEAST->toConfigKey('default'),
                name: TriumphEnum::EDEN_AT_LEAST,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    ActionEnum::TRAVEL_TO_EDEN->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                quantity: 6,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::EDEN_MUSH_INVASION->toConfigKey('default'),
                name: TriumphEnum::EDEN_MUSH_INVASION,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    ActionEnum::TRAVEL_TO_EDEN->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_MUSHS,
                quantity: 32,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::EDEN_MUSH_INTRUDER->toConfigKey('default'),
                name: TriumphEnum::EDEN_MUSH_INTRUDER,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    ActionEnum::TRAVEL_TO_EDEN->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                quantity: -16,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::EDEN_ONE_MAN->toConfigKey('default'),
                name: TriumphEnum::EDEN_ONE_MAN,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    ActionEnum::TRAVEL_TO_EDEN->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                quantity: 1,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::EDEN_ENGINEERS->toConfigKey('default'),
                name: TriumphEnum::EDEN_ENGINEERS,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    ActionEnum::TRAVEL_TO_EDEN->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMAN_TECHNICIANS,
                quantity: 6,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::EDEN_BIOLOGISTS->toConfigKey('default'),
                name: TriumphEnum::EDEN_BIOLOGISTS,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    ActionEnum::TRAVEL_TO_EDEN->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMAN_PHARMACISTS,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::SAVIOR->toConfigKey('default'),
                name: TriumphEnum::SAVIOR,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    ActionEnum::TRAVEL_TO_EDEN->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::PERSONAL_JIN_SU,
                quantity: 8,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::REMEDY->toConfigKey('default'),
                name: TriumphEnum::REMEDY,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                tagConstraints: [
                    ActionEnum::TRAVEL_TO_EDEN->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::PERSONAL_CHUN,
                quantity: 4,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::PRETTY_COOK->toConfigKey('default'),
                name: TriumphEnum::PRETTY_COOK,
                targetedEvent: PlanetSectorEvent::PLANET_SECTOR_EVENT,
                tagConstraints: [
                    PlanetSectorEvent::FIGHT => TriumphSourceEventInterface::ANY_TAG,
                ],
                scope: TriumphScope::PERSONAL_STEPHEN,
                targetSetting: TriumphTarget::ACTIVE_EXPLORERS,
                quantity: 2,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::ALIEN_FRIEND->toConfigKey('default'),
                name: TriumphEnum::ALIEN_FRIEND,
                targetedEvent: PlanetSectorEvent::PLANET_SECTOR_EVENT,
                tagConstraints: [
                    PlanetSectorEnum::INSECT => TriumphSourceEventInterface::ANY_TAG,
                    PlanetSectorEnum::INTELLIGENT => TriumphSourceEventInterface::ANY_TAG,
                    PlanetSectorEnum::MANKAROG => TriumphSourceEventInterface::ANY_TAG,
                    PlanetSectorEnum::PREDATOR => TriumphSourceEventInterface::ANY_TAG,
                    PlanetSectorEnum::RUMINANT => TriumphSourceEventInterface::ANY_TAG,
                    PlanetSectorEvent::FIGHT => TriumphSourceEventInterface::ANY_TAG,
                    PlanetSectorEvent::PROVISION => TriumphSourceEventInterface::ANY_TAG,
                ],
                scope: TriumphScope::PERSONAL_JANICE,
                targetSetting: TriumphTarget::ACTIVE_EXPLORERS,
                quantity: 3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::PREGNANCY->toConfigKey('default'),
                name: TriumphEnum::PREGNANCY,
                targetedEvent: StatusEvent::STATUS_APPLIED,
                tagConstraints: [
                    PlayerStatusEnum::PREGNANT => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                targetSetting: TriumphTarget::EVENT_SUBJECT,
                quantity: 8,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::ALL_PREGNANT->toConfigKey('default'),
                name: TriumphEnum::ALL_PREGNANT,
                targetedEvent: StatusEvent::STATUS_APPLIED,
                tagConstraints: [
                    PlayerStatusEnum::PREGNANT => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                quantity: 2,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::MUSH_PREGNANT->toConfigKey('default'),
                name: TriumphEnum::MUSH_PREGNANT,
                targetedEvent: StatusEvent::STATUS_APPLIED,
                tagConstraints: [
                    PlayerStatusEnum::PREGNANT => TriumphSourceEventInterface::ALL_TAGS,
                    PlayerStatusEnum::MUSH => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_MUSHS,
                quantity: 8,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::NEW_MUSH->toConfigKey('default'),
                name: TriumphEnum::NEW_MUSH,
                targetedEvent: PlayerEvent::CONVERSION_PLAYER,
                tagConstraints: [
                    ActionEnum::EXCHANGE_BODY->toString() => TriumphSourceEventInterface::NONE_TAGS,
                ],
                scope: TriumphScope::ALL_MUSHS,
                targetSetting: TriumphTarget::AUTHOR,
                quantity: 8,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::CYCLE_MUSH_LATE->toConfigKey('default'),
                name: TriumphEnum::CYCLE_MUSH_LATE,
                targetedEvent: PlayerEvent::CONVERSION_PLAYER,
                tagConstraints: [
                    ActionEnum::EXCHANGE_BODY->toString() => TriumphSourceEventInterface::NONE_TAGS,
                    DaedalusEvent::FULL_DAEDALUS => TriumphSourceEventInterface::NONE_TAGS,
                ],
                scope: TriumphScope::ALL_MUSHS,
                targetSetting: TriumphTarget::EVENT_SUBJECT,
                quantity: -3,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::INFECT->toConfigKey('default'),
                name: TriumphEnum::INFECT,
                targetedEvent: PlayerEvent::INFECTION_PLAYER,
                scope: TriumphScope::ALL_MUSHS,
                targetSetting: TriumphTarget::AUTHOR,
                quantity: 1,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::NICE_SURGERY->toConfigKey('default'),
                name: TriumphEnum::NICE_SURGERY,
                targetedEvent: ActionEvent::RESULT_ACTION,
                tagConstraints: [
                    ActionOutputEnum::CRITICAL_SUCCESS => TriumphSourceEventInterface::ALL_TAGS,
                    ActionEnum::SURGERY->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ActionEnum::SELF_SURGERY->toString() => TriumphSourceEventInterface::ANY_TAG,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                targetSetting: TriumphTarget::AUTHOR,
                quantity: 5,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::ROBOTIC_GRAAL->toConfigKey('default'),
                name: TriumphEnum::ROBOTIC_GRAAL,
                targetedEvent: ActionEvent::RESULT_ACTION,
                tagConstraints: [
                    ActionEnum::UPGRADE_DRONE_TO_FIREFIGHTER->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ActionEnum::UPGRADE_DRONE_TO_PILOT->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ActionEnum::UPGRADE_DRONE_TO_SENSOR->toString() => TriumphSourceEventInterface::ANY_TAG,
                    ActionEnum::UPGRADE_DRONE_TO_TURBO->toString() => TriumphSourceEventInterface::ANY_TAG,
                ],
                scope: TriumphScope::PERSONAL_TERRENCE,
                targetSetting: TriumphTarget::AUTHOR,
                quantity: 4,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::REBEL_CONTACT->toConfigKey('default'),
                name: TriumphEnum::REBEL_CONTACT,
                targetedEvent: RebelBaseDecodedEvent::class,
                scope: TriumphScope::PERSONAL_ELEESHA,
                quantity: 2,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::REBEL_WOLF->toConfigKey('default'),
                name: TriumphEnum::REBEL_WOLF,
                targetedEvent: RebelBaseDecodedEvent::class,
                tagConstraints: [
                    RebelBaseEnum::WOLF->toString() => TriumphSourceEventInterface::ALL_TAGS,
                ],
                scope: TriumphScope::ALL_ALIVE_HUMANS,
                quantity: 8,
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
