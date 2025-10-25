import { getImgUrl } from "@/utils/getImgUrl";

//For icons used in the UI (HP, PA, PM, triumph,etc) and logs (same as before + schrodinger meows, fires being put out, hero dying, etc). Most of this list was originally hard coded into formatText, they are being moved here to clean it and the images root folder.

export enum UiIconEnum {
    //Health Points
    HP = 'hp',
    PV = 'pv', //Alternative spelling for French players
    //Action Points
    PA = 'pa',
    AP = 'ap', //Alternative spelling for English players
    //Movement Points
    PM = 'pm',
    MP = 'mp', //Alternative spelling for English players
    //Morale Points
    PMO = 'pmo',
    MORALE = 'morale', //Alternative spelling for English players
    MORAL = 'moral', //Alternative spelling for French players
    //Triumph, ship banner
    TRIUMPH = 'triumph',
    TRIOMPHE = 'triomphe', //Alternative spelling for French players
    //Mush Triumph, ship banner
    TRIUMPH_MUSH = 'triumph_mush',
    TRIOMPHE_MUSH = 'triomphe_mush', //Alternative spelling for French players
    //Disease Symptom Log
    PILL = 'pill',
    //Obtained Injury Log
    HURT = 'hurt',
    //Obtained Psy Disease Log
    PSY_DISEASE = 'psy_disease',
    //Hungry Log
    HUNGRY = 'hungry',
    //public channel icon
    WALL = 'wall',
    BOOK_OPEN = 'book_open', //Alternative spelling for players
    //Tips channel icon
    TIP = 'tip',
    LIGHTBULB = 'lightbulb',
    //Used in public chat tooltip
    TALKIE = 'talkie',
    //Used in the automatically generated chat message when sharing a planet
    PLANET = 'planet',
    //Fuel, ship banner and planet share
    FUEL = 'fuel',
    //Skill descriptions bullet point, <ul> bullet point
    POINT = 'point',
    //<li> bullet point
    POINT_2 = 'point2',

    ONLINE = 'online',
    OFFLINE = 'offline',

    LEFT = 'left',
    RIGHT = 'right',

    DEAD = 'dead',
    FIRE = 'fire',
    CAT = 'cat',

    //isometric icons obtained from researches
    MYCOALARM = 'mycoalarm',
    //anti-mush Serum
    RETRO_FUNGAL_SERUM = 'retro_fungal_serum',
    SERUM = 'serum', //Alternative spellings for players
    RFS = 'rfs',
    SRF = 'srf',

    SPORE_SUCKER = 'spore_sucker',

    HULL = 'hull', //alternatives for alerts/low_hull
    SHIELD = 'shield',

    //watch icon from the cycle timer
    CASIO = 'casio',
    WATCH = 'watch', //Alternative spelling for players

    DOG = 'dog',

    BETA = 'beta',
}

export const UiIconIcons: {[index: string]: string} = {
    [UiIconEnum.HP]: getImgUrl('ui_icons/player_variables/hp.png'),
    [UiIconEnum.PV]: getImgUrl('ui_icons/player_variables/hp.png'),
    [UiIconEnum.PA]: getImgUrl('ui_icons/player_variables/pa.png'),
    [UiIconEnum.AP]: getImgUrl('ui_icons/player_variables/pa.png'),
    [UiIconEnum.PM]: getImgUrl('ui_icons/player_variables/pm.png'),
    [UiIconEnum.MP]: getImgUrl('ui_icons/player_variables/pm.png'),
    [UiIconEnum.PMO]: getImgUrl('ui_icons/player_variables/moral.png'),
    [UiIconEnum.MORALE]: getImgUrl('ui_icons/player_variables/moral.png'),
    [UiIconEnum.MORAL]: getImgUrl('ui_icons/player_variables/moral.png'),
    [UiIconEnum.TRIUMPH]: getImgUrl('ui_icons/player_variables/triumph.png'),
    [UiIconEnum.TRIOMPHE]: getImgUrl('ui_icons/player_variables/triumph.png'),
    [UiIconEnum.TRIUMPH_MUSH]: getImgUrl('ui_icons/player_variables/triumph_mush.png'),
    [UiIconEnum.TRIOMPHE_MUSH]: getImgUrl('ui_icons/player_variables/triumph_mush.png'),
    [UiIconEnum.PILL]: getImgUrl('ui_icons/pill.png'),
    [UiIconEnum.HURT]: getImgUrl('status/injury.png'),
    [UiIconEnum.PSY_DISEASE]: getImgUrl('status/disorder.png'),
    [UiIconEnum.HUNGRY]: getImgUrl('status/starving.png'),
    [UiIconEnum.WALL]: getImgUrl('comms/wall.png'),
    [UiIconEnum.BOOK_OPEN]: getImgUrl('comms/wall.png'),
    [UiIconEnum.TIP]: getImgUrl('comms/tip.png'),
    [UiIconEnum.LIGHTBULB]: getImgUrl('comms/tip.png'),
    [UiIconEnum.TALKIE]: getImgUrl('comms/talkie.png'),
    [UiIconEnum.PLANET]: getImgUrl('ui_icons/planet.png'),
    [UiIconEnum.FUEL]: getImgUrl('ui_icons/fuel.png'),
    [UiIconEnum.POINT]: getImgUrl('ui_icons/point.png'),
    [UiIconEnum.POINT_2]: getImgUrl('ui_icons/point2.png'),
    [UiIconEnum.ONLINE]: getImgUrl('comms/online.gif'),
    [UiIconEnum.OFFLINE]: getImgUrl('comms/offline.gif'),
    [UiIconEnum.LEFT]: getImgUrl('ui_icons/left.png'),
    [UiIconEnum.RIGHT]: getImgUrl('ui_icons/right.png'),
    [UiIconEnum.DEAD]: getImgUrl('ui_icons/dead.png'),
    [UiIconEnum.FIRE]: getImgUrl('alerts/fire.png'),
    [UiIconEnum.CAT]: getImgUrl('char/body/cat.png'),
    [UiIconEnum.MYCOALARM]: getImgUrl('ui_icons/mycoalarm.png'),
    [UiIconEnum.RETRO_FUNGAL_SERUM]: getImgUrl('ui_icons/rf_serum.png'),
    [UiIconEnum.SERUM]: getImgUrl('ui_icons/rf_serum.png'),
    [UiIconEnum.RFS]: getImgUrl('ui_icons/rf_serum.png'),
    [UiIconEnum.SRF]: getImgUrl('ui_icons/rf_serum.png'),
    [UiIconEnum.SPORE_SUCKER]: getImgUrl('ui_icons/spore_sucker.png'),
    [UiIconEnum.HULL]: getImgUrl('shield.png'),
    [UiIconEnum.SHIELD]: getImgUrl('shield.png'),
    [UiIconEnum.CASIO]: getImgUrl('casio.png'),
    [UiIconEnum.WATCH]: getImgUrl('casio.png'),
    [UiIconEnum.DOG]: getImgUrl('ui_icons/dog.png'),
    [UiIconEnum.BETA]: getImgUrl('ui_icons/beta_icon_small.png')
}
;
