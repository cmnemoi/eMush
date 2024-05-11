import { getImgUrl } from '../utils/getImgUrl';

const PLASTIC_SCRAP = "plastic_scraps";
const METAL_SCRAP = "metal_scraps";
const ANTIGRAV_SCOOTER = "antigrav_scooter";
const MEDIKIT = "medikit";
const OSCILLOSCOPE = 'oscilloscope';
const ECHOLOCATOR = 'echolocator';

const BACTA = 'bacta';
const BETAPROPYL = 'betapropyl';
const EUFURYLATE = 'eufurylate';
const NEWKE = 'newke';
const PHUXX = 'phuxx';
const PINQ = 'pinq';
const PYMP = 'pymp';
const ROSEBUD = 'rosebud';
const SOMA = 'soma';
const SPYCE = 'spyce';
const TWINOID = 'twinoid';
const XENOX = 'xenox';

const CREEPNUT = "creepnut";
const BANANA = "banana";
const MEZTINE = 'meztine';
const GUNTIFLOP = 'guntiflop';
const PLOSHMINA = 'ploshmina';
const PRECATI = 'precati';
const BOTTINE = 'bottine';
const FRAGILANE = 'fragilane';
const ANEMOLE = 'anemole';
const PENICRAFT = 'peniraft';
const KUBINUS = 'kubinus';
const CALEBOOT = 'caleboot';
const FILANDRA = 'filandra';
const JUMPKIN = 'jumpkin';

const CREEPNUT_TREE = "creepist";
const BANANA_TREE = "banana_tree";
const CACTAX = 'cactax';
const BIFFLON = 'bifflon';
const PULMMINAGRO = 'pulminagro';
const PRECATUS = 'precatus';
const BUTTALIEN = 'buttalien';
const PLATACIA = 'platacia';
const TUBILISCUS = 'tubiliscus';
const GRAAPSHOOT = 'graapshoot';
const FIBONICCUS = 'fiboniccus';
const MYCOPIA = 'mycopia';
const ASPERAGUNK = 'asperagunk';
const BUMPJUMPKIN = 'bumpjumpkin';

const BLUEPRINT = "blueprint";
const SNIPER_HELMET = "sniper_helmet";
const APPRENTON = "apprenton";
const MAD_KUBE = "mad_kube";
const MICROWAVE = "microwave";
const SUPERFREEZER = "superfreezer";
const HYDROPOT = "hydropot";
const PLASTENITE_ARMOR = "plastenite_armor";
const CAMERA_ITEM = "camera_item";
const EXTINGUISHER = "extinguisher";
const DUCT_TAPE = "duct_tape";
const HACKER_KIT = "hacker_kit";
const QUADRIMETRIC_COMPASS = "quadrimetric_compass";
const ADJUSTABLE_WRENCH = "adjustable_wrench";
const APRON = "stainproof_apron";
const BLOCK_POST_IT = "block_of_post_it";
const POST_IT = "post_it";
const ROPE = "rope";
const DRILL = "drill";
const GLOVES = "protective_gloves";
const SOAP = "soap";
const TABULATRIX = "tabulatrix";
const THICK_TUBE = "thick_tube";
const OXYGEN_CAPSULE = "oxygen_capsule";
const FUEL_CAPSULE = "fuel_capsule";
const SPACE_CAPSULE = "space_capsule";
const BANDAGE = "bandage";
const SPORE_SUCKER = "spore_sucker";
const SPACESUIT = "spacesuit";
const THERMOSENSOR = "thermosensor";
const OLD_T_SHIRT = "old_t_shirt";
const BABEL_MODULE = "babel_module";
const WHITE_FLAG = "white_flag";

const ITRACKIE = "itrackie";
const TRACKER = "tracker";
const WALKIE_TALKIE = "walkie_talkie";

// artefacts
const ALIEN_BOTTLE_OPENER = 'alien_bottle_opener';
const ALIEN_HOLOGRAPHIC_TV = "alien_holographic_tv";
const INVERTEBRATE_SHELL = "invertebrate_shell";
const JAR_OF_ALIEN_OIL = "jar_of_alien_oil";
const MAGELLAN_LIQUID_MAP = "magellan_liquid_map";
const PRINTED_CIRCUIT_JELLY = "printed_circuit_jelly";
const ROLLING_BOULDER = 'rolling_boulder';
const STARMAP_FRAGMENT = "starmap_fragment";
const WATER_STICK = "water_stick";

// weapons
const BLASTER = "blaster";
const GRENADE = "grenade";
const KNIFE = "knife";
const LIZARO_JUNGLE = "lizaro_jungle";
const NATAMY_RIFLE = "natamy_rifle";
const OLD_FAITHFUL = "old_faithful";
const ROCKET_LAUNCHER = "rocket_launcher";

// food
const COFFEE = "coffee";
const STANDARD_RATION = "standard_ration";
const COOKED_RATION = "cooked_ration";
const ORGANIC_WASTE = "organic_waste";
const ALIEN_STEAK = "alien_steak";


export const itemEnum: {[index: string]: any} = {
    [ALIEN_BOTTLE_OPENER]: {
        'image': getImgUrl('items/alien_can_opener.jpg')
    },
    [ECHOLOCATOR]: {
        'image': getImgUrl('items/echo_sounder.jpg')
    },
    [ROLLING_BOULDER]: {
        'image': getImgUrl('items/rolling_boulder.jpg')
    },
    [OSCILLOSCOPE]: {
        'image': getImgUrl('items/wavoscope.jpg')
    },
    [MEDIKIT]: {
        'image': getImgUrl('items/medikit.jpg')
    },
    [BANDAGE]: {
        'image': getImgUrl('items/bandage.jpg')
    },
    [ANTIGRAV_SCOOTER]: {
        'image': getImgUrl('items/antigrav_scooter.jpg')
    },
    [JAR_OF_ALIEN_OIL]: {
        'image': getImgUrl('items/alien_oil.jpg')
    },
    [COFFEE]: {
        'image': getImgUrl('items/coffee.jpg')
    },
    [METAL_SCRAP]: {
        'image': getImgUrl('items/metal_scraps.jpg')
    },
    [PLASTIC_SCRAP]: {
        'image': getImgUrl('items/plastic_scraps.jpg')
    },
    [APPRENTON]: {
        'image': getImgUrl('items/book.jpg')
    },
    [BLUEPRINT]: {
        'image': getImgUrl('items/blueprint.jpg')
    },
    [SNIPER_HELMET]: {
        'image': getImgUrl('items/aiming_helmet.jpg')
    },
    [STANDARD_RATION]: {
        'image': getImgUrl('items/ration_0.jpg')
    },
    [COOKED_RATION]: {
        'image': getImgUrl('items/ration_1.jpg')
    },
    [MAD_KUBE]: {
        'image': getImgUrl('items/mad_kube.jpg')
    },
    [MICROWAVE]: {
        'image': getImgUrl('items/microwave.jpg')
    },
    [SUPERFREEZER]: {
        'image': getImgUrl('items/freezer.jpg')
    },
    [HYDROPOT]: {
        'image': getImgUrl('items/tree_pot.jpg')
    },

    [TWINOID]: {
        'image': getImgUrl('items/drug/drug_0.jpg')
    },
    [XENOX]: {
        'image': getImgUrl('items/drug/drug_1.jpg')
    },
    [PHUXX]: {
        'image': getImgUrl('items/drug/drug_2.jpg')
    },
    [EUFURYLATE]: {
        'image': getImgUrl('items/drug/drug_3.jpg')
    },
    [SOMA]: {
        'image': getImgUrl('items/drug/drug_4.jpg')
    },
    [SPYCE]: {
        'image': getImgUrl('items/drug/drug_5.jpg')
    },
    [NEWKE]: {
        'image': getImgUrl('items/drug/drug_6.jpg')
    },
    [PINQ]: {
        'image': getImgUrl('items/drug/drug_7.jpg')
    },
    [BACTA]: {
        'image': getImgUrl('items/drug/drug_8.jpg')
    },
    [BETAPROPYL]: {
        'image': getImgUrl('items/drug/drug_9.jpg')
    },
    [PYMP]: {
        'image': getImgUrl('items/drug/drug_10.jpg')
    },
    [ROSEBUD]: {
        'image': getImgUrl('items/drug/drug_11.jpg')
    },

    [BANANA_TREE]: {
        'image': getImgUrl('items/plant/fruit_tree00.jpg')
    },
    [BANANA]: {
        'image': getImgUrl('items/fruit/fruit00.jpg')
    },
    [CREEPNUT_TREE]: {
        'image': getImgUrl('items/plant/fruit_tree01.jpg')
    },
    [CREEPNUT]: {
        'image': getImgUrl('items/fruit/fruit01.jpg')
    },
    [CACTAX]: {
        'image': getImgUrl('items/plant/fruit_tree02.jpg')
    },
    [MEZTINE]: {
        'image': getImgUrl('items/fruit/fruit02.jpg')
    },
    [BIFFLON]: {
        'image': getImgUrl('items/plant/fruit_tree03.jpg')
    },
    [GUNTIFLOP]: {
        'image': getImgUrl('items/fruit/fruit03.jpg')
    },
    [PULMMINAGRO]: {
        'image': getImgUrl('items/plant/fruit_tree04.jpg')
    },
    [PLOSHMINA]: {
        'image': getImgUrl('items/fruit/fruit04.jpg')
    },
    [PRECATUS]: {
        'image': getImgUrl('items/plant/fruit_tree05.jpg')
    },
    [PRECATI]: {
        'image': getImgUrl('items/fruit/fruit05.jpg')
    },
    [BUTTALIEN]: {
        'image': getImgUrl('items/plant/fruit_tree06.jpg')
    },
    [BOTTINE]: {
        'image': getImgUrl('items/fruit/fruit06.jpg')
    },
    [PLATACIA]: {
        'image': getImgUrl('items/plant/fruit_tree07.jpg')
    },
    [FRAGILANE]: {
        'image': getImgUrl('items/fruit/fruit07.jpg')
    },
    [TUBILISCUS]: {
        'image': getImgUrl('items/plant/fruit_tree08.jpg')
    },
    [ANEMOLE]: {
        'image': getImgUrl('items/fruit/fruit08.jpg')
    },
    [GRAAPSHOOT]: {
        'image': getImgUrl('items/plant/fruit_tree09.jpg')
    },
    [PENICRAFT]: {
        'image': getImgUrl('items/fruit/fruit09.jpg')
    },
    [FIBONICCUS]: {
        'image': getImgUrl('items/plant/fruit_tree10.jpg')
    },
    [KUBINUS]: {
        'image': getImgUrl('items/fruit/fruit10.jpg')
    },
    [MYCOPIA]: {
        'image': getImgUrl('items/plant/fruit_tree11.jpg')
    },
    [CALEBOOT]: {
        'image': getImgUrl('items/fruit/fruit11.jpg')
    },
    [ASPERAGUNK]: {
        'image': getImgUrl('items/plant/fruit_tree12.jpg')
    },
    [FILANDRA]: {
        'image': getImgUrl('items/fruit/fruit12.jpg')
    },
    [BUMPJUMPKIN]: {
        'image': getImgUrl('items/plant/fruit_tree13.jpg')
    },
    [JUMPKIN]: {
        'image': getImgUrl('items/fruit/fruit13.jpg')
    },

    [PLASTENITE_ARMOR]: {
        'image': getImgUrl('items/plastenite_armor.jpg')
    },
    [CAMERA_ITEM]: {
        'image': getImgUrl('items/camera.jpg')
    },
    [EXTINGUISHER]: {
        'image': getImgUrl('items/extinguisher.jpg')
    },
    [DUCT_TAPE]: {
        'image': getImgUrl('items/duck_tape.jpg')
    },
    [BLASTER]: {
        'image': getImgUrl('items/blaster.jpg')
    },
    [HACKER_KIT]: {
        'image': getImgUrl('items/hacker_kit.jpg')
    },
    [GRENADE]: {
        'image': getImgUrl('items/grenade.jpg')
    },
    [QUADRIMETRIC_COMPASS]: {
        'image': getImgUrl('items/quad_compass.jpg')
    },
    [ADJUSTABLE_WRENCH]: {
        'image': getImgUrl('items/wrench.jpg')
    },
    [APRON]: {
        'image': getImgUrl('items/apron.jpg')
    },
    [BLOCK_POST_IT]: {
        'image': getImgUrl('items/postit_bloc.jpg')
    },
    [POST_IT]: {
        'image': getImgUrl('items/postit.jpg')
    },
    [ROPE]: {
        'image': getImgUrl('items/rope.jpg')
    },
    [DRILL]: {
        'image': getImgUrl('items/driller.jpg')
    },
    [KNIFE]: {
        'image': getImgUrl('items/knife.jpg')
    },
    [GLOVES]: {
        'image': getImgUrl('items/protection_gloves.jpg')
    },
    [SOAP]: {
        'image': getImgUrl('items/soap.jpg')
    },
    [TABULATRIX]: {
        'image': getImgUrl('items/printer.jpg')
    },
    [OXYGEN_CAPSULE]: {
        'image': getImgUrl('items/oxy_capsule.jpg')
    },
    [FUEL_CAPSULE]: {
        'image': getImgUrl('items/fuel_capsule.jpg')
    },
    [THICK_TUBE]: {
        'image': getImgUrl('items/thick_tube.jpg')
    },
    [SPACE_CAPSULE]: {
        'image': getImgUrl('items/space_capsule.jpg')
    },
    [SPORE_SUCKER]: {
        'image': getImgUrl('items/spore_sucker.jpg')
    },
    [ALIEN_HOLOGRAPHIC_TV]: {
        'image': getImgUrl('items/alien_holographic_tv.jpg')
    },
    [WALKIE_TALKIE]: {
        'image': getImgUrl('items/walkie_talkie.jpg')
    },
    [TRACKER]: {
        'image': getImgUrl('items/tracker.jpg')
    },
    [ITRACKIE]: {
        'image': getImgUrl('items/super_talkie.jpg')
    },
    [ORGANIC_WASTE]: {
        'image': getImgUrl('items/organic_waste.jpg')
    },
    [SPACESUIT]: {
        'image': getImgUrl('items/space_suit.jpg')
    },
    [MAGELLAN_LIQUID_MAP]: {
        'image': getImgUrl('items/magellan_liquid_map.jpg')
    },
    [STARMAP_FRAGMENT]: {
        'image': getImgUrl('items/super_map.jpg')
    },
    [WATER_STICK]: {
        'image': getImgUrl('items/water_stick.jpg')
    },
    [INVERTEBRATE_SHELL]: {
        'image': getImgUrl('items/insectoid_shell.jpg')
    },
    [PRINTED_CIRCUIT_JELLY]: {
        'image': getImgUrl('items/computer_jelly.jpg')
    },
    [OLD_FAITHFUL]: {
        'image': getImgUrl('items/machine_gun.jpg')
    },
    [ROCKET_LAUNCHER]: {
        'image': getImgUrl('items/missile_launcher.jpg')
    },
    [LIZARO_JUNGLE]: {
        'image': getImgUrl('items/sniper_riffle.jpg')
    },
    [NATAMY_RIFLE]: {
        'image': getImgUrl('items/natamy_riffle.jpg')
    },
    [ALIEN_STEAK]: {
        'image': getImgUrl('items/ration_5.jpg')
    },
    [THERMOSENSOR]: {
        'image': getImgUrl('items/heat_seeker.jpg')
    },
    [OLD_T_SHIRT]: {
        'image': getImgUrl('items/old_shirt.jpg')
    },
    [BABEL_MODULE]: {
        'image': getImgUrl('items/trad_module.jpg')
    },
    [WHITE_FLAG]: {
        'image': getImgUrl('items/white_flag.jpg')
    }
};
