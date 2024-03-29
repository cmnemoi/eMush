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
        'image': 'src/assets/images/items/alien_can_opener.png'
    },
    [ECHOLOCATOR]: {
        'image': 'src/assets/images/items/echo_sounder.png'
    },
    [ROLLING_BOULDER]: {
        'image': 'src/assets/images/items/rolling_boulder.png'
    },
    [OSCILLOSCOPE]: {
        'image': 'src/assets/images/items/wavoscope.png'
    },
    [MEDIKIT]: {
        'image': 'src/assets/images/items/medikit.png'
    },
    [BANDAGE]: {
        'image': 'src/assets/images/items/bandage.png'
    },
    [ANTIGRAV_SCOOTER]: {
        'image': 'src/assets/images/items/antigrav_scooter.png'
    },
    [JAR_OF_ALIEN_OIL]: {
        'image': 'src/assets/images/items/alien_oil.png'
    },
    [COFFEE]: {
        'image': 'src/assets/images/items/coffee.png'
    },
    [METAL_SCRAP]: {
        'image': 'src/assets/images/items/metal_scraps.png'
    },
    [PLASTIC_SCRAP]: {
        'image': 'src/assets/images/items/plastic_scraps.png'
    },
    [APPRENTON]: {
        'image': 'src/assets/images/items/book.png'
    },
    [BLUEPRINT]: {
        'image': 'src/assets/images/items/blueprint.png'
    },
    [SNIPER_HELMET]: {
        'image': 'src/assets/images/items/aiming_helmet.png'
    },
    [STANDARD_RATION]: {
        'image': 'src/assets/images/items/ration_0.png'
    },
    [COOKED_RATION]: {
        'image': 'src/assets/images/items/ration_1.png'
    },
    [MAD_KUBE]: {
        'image': 'src/assets/images/items/mad_kube.png'
    },
    [MICROWAVE]: {
        'image': 'src/assets/images/items/microwave.png'
    },
    [SUPERFREEZER]: {
        'image': 'src/assets/images/items/freezer.png'
    },
    [HYDROPOT]: {
        'image': 'src/assets/images/items/tree_pot.png'
    },

    [TWINOID]: {
        'image': 'src/assets/images/items/drug/drug_0.png'
    },
    [XENOX]: {
        'image': 'src/assets/images/items/drug/drug_1.png'
    },
    [PHUXX]: {
        'image': 'src/assets/images/items/drug/drug_2.png'
    },
    [EUFURYLATE]: {
        'image': 'src/assets/images/items/drug/drug_3.png'
    },
    [SOMA]: {
        'image': 'src/assets/images/items/drug/drug_4.png'
    },
    [SPYCE]: {
        'image': 'src/assets/images/items/drug/drug_5.png'
    },
    [NEWKE]: {
        'image': 'src/assets/images/items/drug/drug_6.png'
    },
    [PINQ]: {
        'image': 'src/assets/images/items/drug/drug_7.png'
    },
    [BACTA]: {
        'image': 'src/assets/images/items/drug/drug_8.png'
    },
    [BETAPROPYL]: {
        'image': 'src/assets/images/items/drug/drug_9.png'
    },
    [PYMP]: {
        'image': 'src/assets/images/items/drug/drug_10.png'
    },
    [ROSEBUD]: {
        'image': 'src/assets/images/items/drug/drug_11.png'
    },

    [BANANA_TREE]: {
        'image': 'src/assets/images/items/plant/fruit_tree00.png'
    },
    [BANANA]: {
        'image': 'src/assets/images/items/fruit/fruit00.png'
    },
    [CREEPNUT_TREE]: {
        'image': 'src/assets/images/items/plant/fruit_tree01.png'
    },
    [CREEPNUT]: {
        'image': 'src/assets/images/items/fruit/fruit01.png'
    },
    [CACTAX]: {
        'image': 'src/assets/images/items/plant/fruit_tree02.png'
    },
    [MEZTINE]: {
        'image': 'src/assets/images/items/fruit/fruit02.png'
    },
    [BIFFLON]: {
        'image': 'src/assets/images/items/plant/fruit_tree03.png'
    },
    [GUNTIFLOP]: {
        'image': 'src/assets/images/items/fruit/fruit03.png'
    },
    [PULMMINAGRO]: {
        'image': 'src/assets/images/items/plant/fruit_tree04.png'
    },
    [PLOSHMINA]: {
        'image': 'src/assets/images/items/fruit/fruit04.png'
    },
    [PRECATUS]: {
        'image': 'src/assets/images/items/plant/fruit_tree05.png'
    },
    [PRECATI]: {
        'image': 'src/assets/images/items/fruit/fruit05.png'
    },
    [BUTTALIEN]: {
        'image': 'src/assets/images/items/plant/fruit_tree06.png'
    },
    [BOTTINE]: {
        'image': 'src/assets/images/items/fruit/fruit06.png'
    },
    [PLATACIA]: {
        'image': 'src/assets/images/items/plant/fruit_tree07.png'
    },
    [FRAGILANE]: {
        'image': 'src/assets/images/items/fruit/fruit07.png'
    },
    [TUBILISCUS]: {
        'image': 'src/assets/images/items/plant/fruit_tree08.png'
    },
    [ANEMOLE]: {
        'image': 'src/assets/images/items/fruit/fruit08.png'
    },
    [GRAAPSHOOT]: {
        'image': 'src/assets/images/items/plant/fruit_tree09.png'
    },
    [PENICRAFT]: {
        'image': 'src/assets/images/items/fruit/fruit09.png'
    },
    [FIBONICCUS]: {
        'image': 'src/assets/images/items/plant/fruit_tree10.png'
    },
    [KUBINUS]: {
        'image': 'src/assets/images/items/fruit/fruit10.png'
    },
    [MYCOPIA]: {
        'image': 'src/assets/images/items/plant/fruit_tree11.png'
    },
    [CALEBOOT]: {
        'image': 'src/assets/images/items/fruit/fruit11.png'
    },
    [ASPERAGUNK]: {
        'image': 'src/assets/images/items/plant/fruit_tree12.png'
    },
    [FILANDRA]: {
        'image': 'src/assets/images/items/fruit/fruit12.png'
    },
    [BUMPJUMPKIN]: {
        'image': 'src/assets/images/items/plant/fruit_tree13.png'
    },
    [JUMPKIN]: {
        'image': 'src/assets/images/items/fruit/fruit13.png'
    },

    [PLASTENITE_ARMOR]: {
        'image': 'src/assets/images/items/plastenite_armor.png'
    },
    [CAMERA_ITEM]: {
        'image': 'src/assets/images/items/camera.png'
    },
    [EXTINGUISHER]: {
        'image': 'src/assets/images/items/extinguisher.png'
    },
    [DUCT_TAPE]: {
        'image': 'src/assets/images/items/duck_tape.png'
    },
    [BLASTER]: {
        'image': 'src/assets/images/items/blaster.png'
    },
    [HACKER_KIT]: {
        'image': 'src/assets/images/items/hacker_kit.png'
    },
    [GRENADE]: {
        'image': 'src/assets/images/items/grenade.png'
    },
    [QUADRIMETRIC_COMPASS]: {
        'image': 'src/assets/images/items/quad_compass.png'
    },
    [ADJUSTABLE_WRENCH]: {
        'image': 'src/assets/images/items/wrench.png'
    },
    [APRON]: {
        'image': 'src/assets/images/items/apron.png'
    },
    [BLOCK_POST_IT]: {
        'image': 'src/assets/images/items/postit_bloc.png'
    },
    [POST_IT]: {
        'image': 'src/assets/images/items/postit.png'
    },
    [ROPE]: {
        'image': 'src/assets/images/items/rope.png'
    },
    [DRILL]: {
        'image': 'src/assets/images/items/driller.png'
    },
    [KNIFE]: {
        'image': 'src/assets/images/items/knife.png'
    },
    [GLOVES]: {
        'image': 'src/assets/images/items/protection_gloves.png'
    },
    [SOAP]: {
        'image': 'src/assets/images/items/soap.png'
    },
    [TABULATRIX]: {
        'image': 'src/assets/images/items/printer.png'
    },
    [OXYGEN_CAPSULE]: {
        'image': 'src/assets/images/items/oxy_capsule.png'
    },
    [FUEL_CAPSULE]: {
        'image': 'src/assets/images/items/fuel_capsule.png'
    },
    [THICK_TUBE]: {
        'image': 'src/assets/images/items/thick_tube.png'
    },
    [SPACE_CAPSULE]: {
        'image': 'src/assets/images/items/space_capsule.png'
    },
    [SPORE_SUCKER]: {
        'image': 'src/assets/images/items/spore_sucker.png'
    },
    [ALIEN_HOLOGRAPHIC_TV]: {
        'image': 'src/assets/images/items/alien_holographic_tv.png'
    },
    [WALKIE_TALKIE]: {
        'image': 'src/assets/images/items/walkie_talkie.png'
    },
    [TRACKER]: {
        'image': 'src/assets/images/items/tracker.png'
    },
    [ITRACKIE]: {
        'image': 'src/assets/images/items/super_talkie.png'
    },
    [ORGANIC_WASTE]: {
        'image': 'src/assets/images/items/organic_waste.png'
    },
    [SPACESUIT]: {
        'image': 'src/assets/images/items/space_suit.png'
    },
    [MAGELLAN_LIQUID_MAP]: {
        'image': 'src/assets/images/items/magellan_liquid_map.png'
    },
    [STARMAP_FRAGMENT]: {
        'image': 'src/assets/images/items/super_map.png'
    },
    [WATER_STICK]: {
        'image': 'src/assets/images/items/water_stick.png'
    },
    [INVERTEBRATE_SHELL]: {
        'image': 'src/assets/images/items/insectoid_shell.png'
    },
    [PRINTED_CIRCUIT_JELLY]: {
        'image': 'src/assets/images/items/computer_jelly.png'
    },
    [OLD_FAITHFUL]: {
        'image': 'src/assets/images/items/machine_gun.png'
    },
    [ROCKET_LAUNCHER]: {
        'image': 'src/assets/images/items/missile_launcher.png'
    },
    [LIZARO_JUNGLE]: {
        'image': 'src/assets/images/items/sniper_riffle.png'
    },
    [NATAMY_RIFLE]: {
        'image': 'src/assets/images/items/natamy_riffle.png'
    },
    [ALIEN_STEAK]: {
        'image': 'src/assets/images/items/ration_5.png'
    },
    [THERMOSENSOR]: {
        'image': require('@/assets/images/items/heat_seeker.jpg')
    },
    [OLD_T_SHIRT]: {
        'image': require('@/assets/images/items/old_shirt.jpg')
    },
    [BABEL_MODULE]: {
        'image': require('@/assets/images/items/trad_module.jpg')
    },
};
