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

export const EmoteAstroIcons: {[index: string]: {img: string; max_height: string;}}= {
    [EmoteAstroEnum.CAVE]: { img: getImgUrl('astro/cave.png'), max_height: "1.7em" },
    [EmoteAstroEnum.COLD]: { img: getImgUrl('astro/cold.png'), max_height: "1.7em" },
    [EmoteAstroEnum.CRISTALITE]: { img: getImgUrl('astro/cristal_field.png'), max_height: "1.7em" },
    [EmoteAstroEnum.DESERT]: { img: getImgUrl('astro/desert.png'), max_height: "1.7em" },
    [EmoteAstroEnum.FOREST]: { img: getImgUrl('astro/forest.png'), max_height: "1.7em" },
    [EmoteAstroEnum.FUEL]: { img: getImgUrl('astro/hydrocarbon.png'), max_height: "1.7em" },
    [EmoteAstroEnum.HOT]: { img: getImgUrl('astro/hot.png'), max_height: "1.7em" },
    [EmoteAstroEnum.INSECT]: { img: getImgUrl('astro/insect.png'), max_height: "1.7em" },
    [EmoteAstroEnum.INTELLIGENT]: { img: getImgUrl('astro/intelligent.png'), max_height: "1.7em" },
    [EmoteAstroEnum.LANDING]: { img: getImgUrl('astro/landing.png'), max_height: "1.7em" },
    [EmoteAstroEnum.LOST]: { img: getImgUrl('astro/lost.png'), max_height: "1.7em" },
    [EmoteAstroEnum.MANKAROG]: { img: getImgUrl('astro/mankarog.png'), max_height: "1.7em" },
    [EmoteAstroEnum.MOUNTAIN]: { img: getImgUrl('astro/mountain.png'), max_height: "1.7em" },
    [EmoteAstroEnum.OCEAN]: { img: getImgUrl('astro/ocean.png'), max_height: "1.7em" },
    [EmoteAstroEnum.ORCHARD]: { img: getImgUrl('astro/fruit_trees.png'), max_height: "1.7em" },
    [EmoteAstroEnum.OXYGEN]: { img: getImgUrl('astro/oxygen.png'), max_height: "1.7em" },
    [EmoteAstroEnum.PREDATOR]: { img: getImgUrl('astro/predator.png'), max_height: "1.7em" },
    [EmoteAstroEnum.RUINS]: { img: getImgUrl('astro/ruins.png'), max_height: "1.7em" },
    [EmoteAstroEnum.RUMINANT]: { img: getImgUrl('astro/ruminant.png'), max_height: "1.7em" },
    [EmoteAstroEnum.SEISMIC]: { img: getImgUrl('astro/seismic_activity.png'), max_height: "1.7em" },
    [EmoteAstroEnum.SWAMP]: { img: getImgUrl('astro/swamp.png'), max_height: "1.7em" },
    [EmoteAstroEnum.UNKNOWN]: { img: getImgUrl('astro/unknown.png'), max_height: "1.7em" },
    [EmoteAstroEnum.VOLCANO]: { img: getImgUrl('astro/volcanic_activity.png'), max_height: "1.7em" },
    [EmoteAstroEnum.WIND]: { img: getImgUrl('astro/strong_wind.png'), max_height: "1.7em" },
    [EmoteAstroEnum.WRECK]: { img: getImgUrl('astro/wreck.png'), max_height: "1.7em" }
};
