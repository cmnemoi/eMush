<?php

declare(strict_types=1);

namespace Mush\Achievement\Enum;

enum AchievementEnum: string
{
    case GAGGED_1 = 'gagged_1';
    case CAT_CUDDLED_1 = 'cat_cuddled_1';
    case COFFEE_TAKEN_1 = 'coffee_taken_1';
    case DOOR_REPAIRED_1 = 'door_repaired_1';
    case GIVE_MISSION_1 = 'give_mission_1';
    case GIVE_MISSION_10 = 'give_mission_10';
    case GIVE_MISSION_50 = 'give_mission_50';
    case GIVE_MISSION_100 = 'give_mission_100';
    case GIVE_MISSION_500 = 'give_mission_500';
    case EXPLO_FEED_1 = 'explo_feed_1';
    case EXPLO_FEED_50 = 'explo_feed_50';
    case EXPLO_FEED_200 = 'explo_feed_200';
    case EXPLO_FEED_500 = 'explo_feed_500';
    case EXPLO_FEED_1000 = 'explo_feed_1000';
    case NEW_PLANTS_1 = 'new_plants_1';
    case EXPLORER_1 = 'explorer_1';
    case EXPLORER_50 = 'explorer_50';
    case EXPLORER_200 = 'explorer_200';
    case EXPLORER_1000 = 'explorer_1000';
    case ARTEFACT_SPECIALIST_1 = 'artefact_specialist_1';
    case ARTEFACT_SPECIALIST_2 = 'artefact_specialist_2';
    case ARTEFACT_SPECIALIST_3 = 'artefact_specialist_3';
    case ARTEFACT_SPECIALIST_4 = 'artefact_specialist_4';
    case ARTEFACT_SPECIALIST_5 = 'artefact_specialist_5';
    case ARTEFACT_SPECIALIST_6 = 'artefact_specialist_6';
    case ARTEFACT_SPECIALIST_7 = 'artefact_specialist_7';
    case ARTEFACT_SPECIALIST_8 = 'artefact_specialist_8';
    case BACK_TO_ROOT_1 = 'back_to_root_1';
    case PLANET_SCANNED_1 = 'planet_scanned_1';
    case SIGNAL_EQUIP_1 = 'signal_equip_1';
    case SIGNAL_EQUIP_20 = 'signal_equip_20';
    case SIGNAL_EQUIP_50 = 'signal_equip_50';
    case SIGNAL_EQUIP_200 = 'signal_equip_200';
    case SIGNAL_EQUIP_1000 = 'signal_equip_1000';
    case SUCCEEDED_INSPECTION_1 = 'succeeded_inspection_1';
    case ANDIE_50 = 'andie_50';
    case ANDIE_200 = 'andie_200';
    case ANDIE_500 = 'andie_500';
    case ANDIE_2000 = 'andie_2000';
    case ANDIE_10000 = 'andie_10000';
    case CHUN_50 = 'chun_50';
    case CHUN_200 = 'chun_200';
    case CHUN_500 = 'chun_500';
    case CHUN_2000 = 'chun_2000';
    case CHUN_10000 = 'chun_10000';
    case CHAO_50 = 'chao_50';
    case CHAO_200 = 'chao_200';
    case CHAO_500 = 'chao_500';
    case CHAO_2000 = 'chao_2000';
    case CHAO_10000 = 'chao_10000';
    case FINOLA_50 = 'finola_50';
    case FINOLA_200 = 'finola_200';
    case FINOLA_500 = 'finola_500';
    case FINOLA_2000 = 'finola_2000';
    case FINOLA_10000 = 'finola_10000';
    case CONTRIBUTIONS_1 = 'contributions_1';
    case CONTRIBUTIONS_5 = 'contributions_5';
    case CONTRIBUTIONS_20 = 'contributions_20';
    case CONTRIBUTIONS_50 = 'contributions_50';
    case CONTRIBUTIONS_100 = 'contributions_100';
    case CONTRIBUTIONS_300 = 'contributions_300';
    case CONTRIBUTIONS_700 = 'contributions_700';
    case CONTRIBUTIONS_1500 = 'contributions_1500';
    case FRIEDA_50 = 'frieda_50';
    case FRIEDA_200 = 'frieda_200';
    case FRIEDA_500 = 'frieda_500';
    case FRIEDA_2000 = 'frieda_2000';
    case FRIEDA_10000 = 'frieda_10000';
    case IAN_50 = 'ian_50';
    case IAN_200 = 'ian_200';
    case IAN_500 = 'ian_500';
    case IAN_2000 = 'ian_2000';
    case IAN_10000 = 'ian_10000';
    case JANICE_50 = 'janice_50';
    case JANICE_200 = 'janice_200';
    case JANICE_500 = 'janice_500';
    case JANICE_2000 = 'janice_2000';
    case JANICE_10000 = 'janice_10000';
    case NULL = '';

    public function toStatisticName(): StatisticEnum
    {
        $statisticName = preg_replace('/_\d+$/', '', $this->value);
        if (!$statisticName) {
            throw new \Exception("Could not parse statistic name from {$this->value}");
        }

        return StatisticEnum::from($statisticName);
    }
}
