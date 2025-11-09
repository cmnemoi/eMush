import { getImgUrl } from "@/utils/getImgUrl";
import { EmoteCharacterEnum } from "@/enums/emotes/characters.enum";


export enum EmoteAstroEnum {
    CAVE = 'as_cave',
    COLD = 'as_cold',
    CRISTALITE = 'as_cristalite',
    DESERT = 'as_desert',
    FOREST = 'as_forest',
    FUEL = 'as_fuel',
    HOT = 'as_hot',
    INSECT = 'as_insect',
    INTELLIGENT = 'as_intelligent',
    LANDING = 'as_landing',
    LOST = 'as_lost',
    MANKAROG = 'as_mankarog',
    MOUNTAIN = 'as_mountain',
    OCEAN = 'as_ocean',
    ORCHARD = 'as_orchard',
    OXYGEN = 'as_oxygen',
    PREDATOR = 'as_predator',
    RUINS = 'as_ruins',
    RUMINANT = 'as_ruminant',
    SEISMIC = 'as_seismic',
    SWAMP = 'as_swamp',
    UNKNOWN = 'as_unknown',
    VOLCANO = 'as_volcano',
    WIND = 'as_wind',
    WRECK = 'as_wreck',
}

export const EmoteAstroAliases: { [index: string]: string } = { };

export const EmoteAstroIcons: { [index: string]: string } = {
    [EmoteAstroEnum.CAVE]: getImgUrl('astro/cave.png'),
    [EmoteAstroEnum.COLD]: getImgUrl('astro/cold.png'),
    [EmoteAstroEnum.CRISTALITE]: getImgUrl('astro/cristal_field.png'),
    [EmoteAstroEnum.DESERT]: getImgUrl('astro/desert.png'),
    [EmoteAstroEnum.FOREST]: getImgUrl('astro/forest.png'),
    [EmoteAstroEnum.FUEL]: getImgUrl('astro/hydrocarbon.png'),
    [EmoteAstroEnum.HOT]: getImgUrl('astro/hot.png'),
    [EmoteAstroEnum.INSECT]: getImgUrl('astro/insect.png'),
    [EmoteAstroEnum.INTELLIGENT]: getImgUrl('astro/intelligent.png'),
    [EmoteAstroEnum.LANDING]: getImgUrl('astro/landing.png'),
    [EmoteAstroEnum.LOST]: getImgUrl('astro/lost.png'),
    [EmoteAstroEnum.MANKAROG]: getImgUrl('astro/mankarog.png'),
    [EmoteAstroEnum.MOUNTAIN]: getImgUrl('astro/mountain.png'),
    [EmoteAstroEnum.OCEAN]: getImgUrl('astro/ocean.png'),
    [EmoteAstroEnum.ORCHARD]: getImgUrl('astro/fruit_trees.png'),
    [EmoteAstroEnum.OXYGEN]: getImgUrl('astro/oxygen.png'),
    [EmoteAstroEnum.PREDATOR]: getImgUrl('astro/predator.png'),
    [EmoteAstroEnum.RUINS]: getImgUrl('astro/ruins.png'),
    [EmoteAstroEnum.RUMINANT]: getImgUrl('astro/ruminant.png'),
    [EmoteAstroEnum.SEISMIC]: getImgUrl('astro/seismic_activity.png'),
    [EmoteAstroEnum.SWAMP]: getImgUrl('astro/swamp.png'),
    [EmoteAstroEnum.UNKNOWN]: getImgUrl('astro/unknown.png'),
    [EmoteAstroEnum.VOLCANO]: getImgUrl('astro/volcanic_activity.png'),
    [EmoteAstroEnum.WIND]: getImgUrl('astro/strong_wind.png'),
    [EmoteAstroEnum.WRECK]: getImgUrl('astro/wreck.png')
};
