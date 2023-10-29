<?php

declare(strict_types=1);

namespace Mush\MetaGame\Enum;

final class TwinoidAPIFieldsEnum
{
    public const ME_CREATION_DATE = 'creationDate';
    public const ME_HISTORY_HEROES = 'historyHeroes';
    public const ME_HISTORY_SHIPS = 'historyShips';
    public const ME_ID = 'id';
    public const ME_XP = 'xp';

    public const HISTORY_SHIP_CONF = 'conf';
    public const HISTORY_SHIP_COUNTER_ALL_SPORE = 'counter_all_spore';
    public const HISTORY_SHIP_COUNTER_EXPLO = 'counter_explo';
    public const HISTORY_SHIP_COUNTER_HUNTER_DEAD = 'counter_hunter_dead';
    public const HISTORY_SHIP_COUNTER_MUSHES = 'counter_mushes';
    public const HISTORY_SHIP_COUNTER_PLANET_SCANNED = 'counter_planet_scanned';
    public const HISTORY_SHIP_COUNTER_PROJECTS = 'counter_projects';
    public const HISTORY_SHIP_COUNTER_REBEL_BASES = 'counter_rebel_bases';
    public const HISTORY_SHIP_COUNTER_RESEARCH = 'counter_research';
    public const HISTORY_SHIP_CREATION_DATE = 'creationDate';
    public const HISTORY_SHIP_DEATH_CYCLE = 'deathCycle';
    public const HISTORY_SHIP_DESTRUCTION_DATE = 'destructionDate';
    public const HISTORY_SHIP_GROUP = 'group';
    public const HISTORY_SHIP_ID = 'id';
    public const HISTORY_SHIP_PILGRED_DONE = 'pilgredDone';
    public const HISTORY_SHIP_PROJECTS = 'projects';
    public const HISTORY_SHIP_RESEARCHES = 'researches';
    public const HISTORY_SHIP_SEASON = 'season';
    public const HISTORY_SHIP_SHIP_ID = 'shipId';
    public const HISTORY_SHIP_TRIUMPH_REMAP = 'triumphRemap';

    public const HISTORY_HERO_ID = 'id';
    public const HISTORY_HERO_DATE = 'date';
    public const HISTORY_HERO_DEATH_CYCLE = 'deathCycle';
    public const HISTORY_HERO_DEATH_ID = 'deathId';
    public const HISTORY_HERO_DEATH_LOCATION = 'deathLocation';
    public const HISTORY_HERO_EPITAPH = 'epitaph';
    public const HISTORY_HERO_GROUP = 'group';
    public const HISTORY_HERO_HERO_ID = 'heroId';
    public const HISTORY_HERO_LOG = 'log';
    public const HISTORY_HERO_RANK = 'rank';
    public const HISTORY_HERO_SEASON = 'season';
    public const HISTORY_HERO_SHIP_ID = 'shipId';
    public const HISTORY_HERO_SKILL_LIST = 'skillList';
    public const HISTORY_HERO_TRIUMPH = 'triumph';
    public const HISTORY_HERO_USER = 'user';
    public const HISTORY_HERO_WAS_MUSH = 'wasMush';

    public const SEASON_DESC = 'desc';
    public const SEASON_ID = 'id';
    public const SEASON_OPTIONS = 'options';
    public const SEASON_PICTO = 'picto';
    public const SEASON_PUBLIC_NAME = 'publicName';
    public const SEASON_START = 'start';

    public const GROUP_AVATAR = 'avatar';
    public const GROUP_BANNER = 'banner';
    public const GROUP_CREATION = 'creation';
    public const GROUP_DESC = 'desc';
    public const GROUP_DOMAIN = 'domain';
    public const GROUP_ID = 'id';
    public const GROUP_INVESTS = 'invests';
    public const GROUP_NAME = 'name';
    public const GROUP_RESULT_DESC = 'resultDesc';
    public const GROUP_TRIUMPH_REMAP = 'triumphRemap';
    public const GROUP_XP = 'xp';

    public static array $meFields = [
        self::ME_CREATION_DATE,
        self::ME_HISTORY_HEROES,
        self::ME_HISTORY_SHIPS,
        self::ME_ID,
        self::ME_XP,
    ];

    public static array $historyShipFields = [
        self::HISTORY_SHIP_CONF,
        self::HISTORY_SHIP_COUNTER_ALL_SPORE,
        self::HISTORY_SHIP_COUNTER_EXPLO,
        self::HISTORY_SHIP_COUNTER_HUNTER_DEAD,
        self::HISTORY_SHIP_COUNTER_MUSHES,
        self::HISTORY_SHIP_COUNTER_PLANET_SCANNED,
        self::HISTORY_SHIP_COUNTER_PROJECTS,
        self::HISTORY_SHIP_COUNTER_REBEL_BASES,
        self::HISTORY_SHIP_COUNTER_RESEARCH,
        self::HISTORY_SHIP_CREATION_DATE,
        self::HISTORY_SHIP_DEATH_CYCLE,
        self::HISTORY_SHIP_DESTRUCTION_DATE,
        self::HISTORY_SHIP_GROUP,
        self::HISTORY_SHIP_ID,
        self::HISTORY_SHIP_PILGRED_DONE,
        self::HISTORY_SHIP_PROJECTS,
        self::HISTORY_SHIP_RESEARCHES,
        self::HISTORY_SHIP_SEASON,
        self::HISTORY_SHIP_SHIP_ID,
        self::HISTORY_SHIP_TRIUMPH_REMAP,
    ];

    public static array $historyHeroFields = [
        self::HISTORY_HERO_ID,
        self::HISTORY_HERO_DATE,
        self::HISTORY_HERO_DEATH_CYCLE,
        self::HISTORY_HERO_DEATH_ID,
        self::HISTORY_HERO_DEATH_LOCATION,
        self::HISTORY_HERO_EPITAPH,
        self::HISTORY_HERO_GROUP,
        self::HISTORY_HERO_HERO_ID,
        self::HISTORY_HERO_LOG,
        self::HISTORY_HERO_RANK,
        self::HISTORY_HERO_SEASON,
        self::HISTORY_HERO_SHIP_ID,
        self::HISTORY_HERO_SKILL_LIST,
        self::HISTORY_HERO_TRIUMPH,
        self::HISTORY_HERO_USER,
        self::HISTORY_HERO_WAS_MUSH,
    ];

    public static array $seasonFields = [
        self::SEASON_DESC,
        self::SEASON_ID,
        self::SEASON_OPTIONS,
        self::SEASON_PICTO,
        self::SEASON_PUBLIC_NAME,
        self::SEASON_START,
    ];

    public static array $groupFields = [
        self::GROUP_AVATAR,
        self::GROUP_BANNER,
        self::GROUP_CREATION,
        self::GROUP_DESC,
        self::GROUP_DOMAIN,
        self::GROUP_ID,
        self::GROUP_INVESTS,
        self::GROUP_NAME,
        self::GROUP_RESULT_DESC,
        self::GROUP_TRIUMPH_REMAP,
        self::GROUP_XP,
    ];

    public static function buildMushUserFields(): string
    {
        $fields = self::ME_CREATION_DATE . ',' . self::ME_ID . ',' . self::ME_XP . ',';

        $fields .= self::ME_HISTORY_HEROES . '.fields(';
        foreach (self::$historyHeroFields as $field) {
            $fields .= $field . ',';
        }
        $fields = substr($fields, 0, -1);
        $fields .= '),';

        $fields .= self::ME_HISTORY_SHIPS . '.fields(';
        foreach (self::$historyShipFields as $field) {
            if ($field === self::HISTORY_SHIP_GROUP) {
                $fields .= self::HISTORY_SHIP_GROUP . '.fields(';
                foreach (self::$groupFields as $groupField) {
                    $fields .= $groupField . ',';
                }
                $fields = substr($fields, 0, -1);
                $fields .= '),';
            } else if ($field === self::HISTORY_SHIP_SEASON) {
                $fields .= self::HISTORY_SHIP_SEASON . '.fields(';
                foreach (self::$seasonFields as $seasonField) {
                    $fields .= $seasonField . ',';
                }
                $fields = substr($fields, 0, -1);
                $fields .= '),';
            }
            else {
                $fields .= $field . ',';
            }
        }
        $fields = substr($fields, 0, -1);
        $fields .= ')';

        return $fields;
    }

    public static function buildTwinoidUserFields(): string
    {
        return 'id, name, sites.fields(site.fields(name), stats.fields(id, score, name, description, rare), achievements.fields(id, name, stat, score, points, npoints, description, date))';
    }
}
