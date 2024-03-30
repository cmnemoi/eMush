import { getAssetUrl } from '../utils/getAssetUrl';

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
        'image': getAssetUrl('items/alien_can_opener.jpg')
    },
    [ECHOLOCATOR]: {
        'image': getAssetUrl('items/echo_sounder.jpg')
    },
    [ROLLING_BOULDER]: {
        'image': getAssetUrl('items/rolling_boulder.jpg')
    },
    [OSCILLOSCOPE]: {
        'image': getAssetUrl('items/wavoscope.jpg')
    },
    [MEDIKIT]: {
        'image': getAssetUrl('items/medikit.jpg')
    },
    [BANDAGE]: {
        'image': getAssetUrl('items/bandage.jpg')
    },
    [ANTIGRAV_SCOOTER]: {
        'image': getAssetUrl('items/antigrav_scooter.jpg')
    },
    [JAR_OF_ALIEN_OIL]: {
        'image': getAssetUrl('items/alien_oil.jpg')
    },
    [COFFEE]: {
        'image': getAssetUrl('items/coffee.jpg')
    },
    [METAL_SCRAP]: {
        'image': getAssetUrl('items/metal_scraps.jpg')
    },
    [PLASTIC_SCRAP]: {
        'image': getAssetUrl('items/plastic_scraps.jpg')
    },
    [APPRENTON]: {
        'image': getAssetUrl('items/book.jpg')
    },
    [BLUEPRINT]: {
        'image': getAssetUrl('items/blueprint.jpg')
    },
    [SNIPER_HELMET]: {
        'image': getAssetUrl('items/aiming_helmet.jpg')
    },
    [STANDARD_RATION]: {
        'image': getAssetUrl('items/ration_0.jpg')
    },
    [COOKED_RATION]: {
        'image': getAssetUrl('items/ration_1.jpg')
    },
    [MAD_KUBE]: {
        'image': getAssetUrl('items/mad_kube.jpg')
    },
    [MICROWAVE]: {
        'image': getAssetUrl('items/microwave.jpg')
    },
    [SUPERFREEZER]: {
        'image': getAssetUrl('items/freezer.jpg')
    },
    [HYDROPOT]: {
        'image': getAssetUrl('items/tree_pot.jpg')
    },

    [TWINOID]: {
        'image': getAssetUrl('items/drug/drug_0.jpg')
    },
    [XENOX]: {
        'image': getAssetUrl('items/drug/drug_1.jpg')
    },
    [PHUXX]: {
        'image': getAssetUrl('items/drug/drug_2.jpg')
    },
    [EUFURYLATE]: {
        'image': getAssetUrl('items/drug/drug_3.jpg')
    },
    [SOMA]: {
        'image': getAssetUrl('items/drug/drug_4.jpg')
    },
    [SPYCE]: {
        'image': getAssetUrl('items/drug/drug_5.jpg')
    },
    [NEWKE]: {
        'image': getAssetUrl('items/drug/drug_6.jpg')
    },
    [PINQ]: {
        'image': getAssetUrl('items/drug/drug_7.jpg')
    },
    [BACTA]: {
        'image': getAssetUrl('items/drug/drug_8.jpg')
    },
    [BETAPROPYL]: {
        'image': getAssetUrl('items/drug/drug_9.jpg')
    },
    [PYMP]: {
        'image': getAssetUrl('items/drug/drug_10.jpg')
    },
    [ROSEBUD]: {
        'image': getAssetUrl('items/drug/drug_11.jpg')
    },

    [BANANA_TREE]: {
        'image': getAssetUrl('items/plant/fruit_tree00.jpg')
    },
    [BANANA]: {
        'image': getAssetUrl('items/fruit/fruit00.jpg')
    },
    [CREEPNUT_TREE]: {
        'image': getAssetUrl('items/plant/fruit_tree01.jpg')
    },
    [CREEPNUT]: {
        'image': getAssetUrl('items/fruit/fruit01.jpg')
    },
    [CACTAX]: {
        'image': getAssetUrl('items/plant/fruit_tree02.jpg')
    },
    [MEZTINE]: {
        'image': getAssetUrl('items/fruit/fruit02.jpg')
    },
    [BIFFLON]: {
        'image': getAssetUrl('items/plant/fruit_tree03.jpg')
    },
    [GUNTIFLOP]: {
        'image': getAssetUrl('items/fruit/fruit03.jpg')
    },
    [PULMMINAGRO]: {
        'image': getAssetUrl('items/plant/fruit_tree04.jpg')
    },
    [PLOSHMINA]: {
        'image': getAssetUrl('items/fruit/fruit04.jpg')
    },
    [PRECATUS]: {
        'image': getAssetUrl('items/plant/fruit_tree05.jpg')
    },
    [PRECATI]: {
        'image': getAssetUrl('items/fruit/fruit05.jpg')
    },
    [BUTTALIEN]: {
        'image': getAssetUrl('items/plant/fruit_tree06.jpg')
    },
    [BOTTINE]: {
        'image': getAssetUrl('items/fruit/fruit06.jpg')
    },
    [PLATACIA]: {
        'image': getAssetUrl('items/plant/fruit_tree07.jpg')
    },
    [FRAGILANE]: {
        'image': getAssetUrl('items/fruit/fruit07.jpg')
    },
    [TUBILISCUS]: {
        'image': getAssetUrl('items/plant/fruit_tree08.jpg')
    },
    [ANEMOLE]: {
        'image': getAssetUrl('items/fruit/fruit08.jpg')
    },
    [GRAAPSHOOT]: {
        'image': getAssetUrl('items/plant/fruit_tree09.jpg')
    },
    [PENICRAFT]: {
        'image': getAssetUrl('items/fruit/fruit09.jpg')
    },
    [FIBONICCUS]: {
        'image': getAssetUrl('items/plant/fruit_tree10.jpg')
    },
    [KUBINUS]: {
        'image': getAssetUrl('items/fruit/fruit10.jpg')
    },
    [MYCOPIA]: {
        'image': getAssetUrl('items/plant/fruit_tree11.jpg')
    },
    [CALEBOOT]: {
        'image': getAssetUrl('items/fruit/fruit11.jpg')
    },
    [ASPERAGUNK]: {
        'image': getAssetUrl('items/plant/fruit_tree12.jpg')
    },
    [FILANDRA]: {
        'image': getAssetUrl('items/fruit/fruit12.jpg')
    },
    [BUMPJUMPKIN]: {
        'image': getAssetUrl('items/plant/fruit_tree13.jpg')
    },
    [JUMPKIN]: {
        'image': getAssetUrl('items/fruit/fruit13.jpg')
    },

    [PLASTENITE_ARMOR]: {
        'image': getAssetUrl('items/plastenite_armor.jpg')
    },
    [CAMERA_ITEM]: {
        'image': getAssetUrl('items/camera.jpg')
    },
    [EXTINGUISHER]: {
        'image': getAssetUrl('items/extinguisher.jpg')
    },
    [DUCT_TAPE]: {
        'image': getAssetUrl('items/duck_tape.jpg')
    },
    [BLASTER]: {
        'image': getAssetUrl('items/blaster.jpg')
    },
    [HACKER_KIT]: {
        'image': getAssetUrl('items/hacker_kit.jpg')
    },
    [GRENADE]: {
        'image': getAssetUrl('items/grenade.jpg')
    },
    [QUADRIMETRIC_COMPASS]: {
        'image': getAssetUrl('items/quad_compass.jpg')
    },
    [ADJUSTABLE_WRENCH]: {
        'image': getAssetUrl('items/wrench.jpg')
    },
    [APRON]: {
        'image': getAssetUrl('items/apron.jpg')
    },
    [BLOCK_POST_IT]: {
        'image': getAssetUrl('items/postit_bloc.jpg')
    },
    [POST_IT]: {
        'image': getAssetUrl('items/postit.jpg')
    },
    [ROPE]: {
        'image': getAssetUrl('items/rope.jpg')
    },
    [DRILL]: {
        'image': getAssetUrl('items/driller.jpg')
    },
    [KNIFE]: {
        'image': getAssetUrl('items/knife.jpg')
    },
    [GLOVES]: {
        'image': getAssetUrl('items/protection_gloves.jpg')
    },
    [SOAP]: {
        'image': getAssetUrl('items/soap.jpg')
    },
    [TABULATRIX]: {
        'image': getAssetUrl('items/printer.jpg')
    },
    [OXYGEN_CAPSULE]: {
        'image': getAssetUrl('items/oxy_capsule.jpg')
    },
    [FUEL_CAPSULE]: {
        'image': getAssetUrl('items/fuel_capsule.jpg')
    },
    [THICK_TUBE]: {
        'image': getAssetUrl('items/thick_tube.jpg')
    },
    [SPACE_CAPSULE]: {
        'image': getAssetUrl('items/space_capsule.jpg')
    },
    [SPORE_SUCKER]: {
        'image': getAssetUrl('items/spore_sucker.jpg')
    },
    [ALIEN_HOLOGRAPHIC_TV]: {
        'image': getAssetUrl('items/alien_holographic_tv.jpg')
    },
    [WALKIE_TALKIE]: {
        'image': getAssetUrl('items/walkie_talkie.jpg')
    },
    [TRACKER]: {
        'image': getAssetUrl('items/tracker.jpg')
    },
    [ITRACKIE]: {
        'image': getAssetUrl('items/super_talkie.jpg')
    },
    [ORGANIC_WASTE]: {
        'image': getAssetUrl('items/organic_waste.jpg')
    },
    [SPACESUIT]: {
        'image': getAssetUrl('items/space_suit.jpg')
    },
    [MAGELLAN_LIQUID_MAP]: {
        'image': getAssetUrl('items/magellan_liquid_map.jpg')
    },
    [STARMAP_FRAGMENT]: {
        'image': getAssetUrl('items/super_map.jpg')
    },
    [WATER_STICK]: {
        'image': getAssetUrl('items/water_stick.jpg')
    },
    [INVERTEBRATE_SHELL]: {
        'image': getAssetUrl('items/insectoid_shell.jpg')
    },
    [PRINTED_CIRCUIT_JELLY]: {
        'image': getAssetUrl('items/computer_jelly.jpg')
    },
    [OLD_FAITHFUL]: {
        'image': getAssetUrl('items/machine_gun.jpg')
    },
    [ROCKET_LAUNCHER]: {
        'image': getAssetUrl('items/missile_launcher.jpg')
    },
    [LIZARO_JUNGLE]: {
        'image': getAssetUrl('items/sniper_riffle.jpg')
    },
    [NATAMY_RIFLE]: {
        'image': getAssetUrl('items/natamy_riffle.jpg')
    },
    [ALIEN_STEAK]: {
        'image': getAssetUrl('items/ration_5.jpg')
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
};
