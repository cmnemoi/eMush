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
const FIBONICCUS = 'Fiboniccus';
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
        'image': require('@/assets/images/items/alien_can_opener.jpg')
    },
    [ECHOLOCATOR]: {
        'image': require('@/assets/images/items/echo_sounder.jpg')
    },
    [ROLLING_BOULDER]: {
        'image': require('@/assets/images/items/rolling_boulder.jpg')
    },
    [OSCILLOSCOPE]: {
        'image': require('@/assets/images/items/wavoscope.jpg')
    },
    [MEDIKIT]: {
        'image': require('@/assets/images/items/medikit.jpg')
    },
    [BANDAGE]: {
        'image': require('@/assets/images/items/bandage.jpg')
    },
    [ANTIGRAV_SCOOTER]: {
        'image': require('@/assets/images/items/antigrav_scooter.jpg')
    },
    [JAR_OF_ALIEN_OIL]: {
        'image': require('@/assets/images/items/alien_oil.jpg')
    },
    [COFFEE]: {
        'image': require('@/assets/images/items/coffee.jpg')
    },
    [METAL_SCRAP]: {
        'image': require('@/assets/images/items/metal_scraps.jpg')
    },
    [PLASTIC_SCRAP]: {
        'image': require('@/assets/images/items/plastic_scraps.jpg')
    },
    [APPRENTON]: {
        'image': require('@/assets/images/items/book.jpg')
    },
    [BLUEPRINT]: {
        'image': require('@/assets/images/items/blueprint.jpg')
    },
    [SNIPER_HELMET]: {
        'image': require('@/assets/images/items/aiming_helmet.jpg')
    },
    [STANDARD_RATION]: {
        'image': require('@/assets/images/items/ration_0.jpg')
    },
    [COOKED_RATION]: {
        'image': require('@/assets/images/items/ration_1.jpg')
    },
    [MAD_KUBE]: {
        'image': require('@/assets/images/items/mad_kube.jpg')
    },
    [MICROWAVE]: {
        'image': require('@/assets/images/items/microwave.jpg')
    },
    [SUPERFREEZER]: {
        'image': require('@/assets/images/items/freezer.jpg')
    },
    [HYDROPOT]: {
        'image': require('@/assets/images/items/tree_pot.jpg')
    },

    [TWINOID]: {
        'image': require('@/assets/images/items/drug/drug_0.jpg')
    },
    [XENOX]: {
        'image': require('@/assets/images/items/drug/drug_1.jpg')
    },
    [PHUXX]: {
        'image': require('@/assets/images/items/drug/drug_2.jpg')
    },
    [EUFURYLATE]: {
        'image': require('@/assets/images/items/drug/drug_3.jpg')
    },
    [SOMA]: {
        'image': require('@/assets/images/items/drug/drug_4.jpg')
    },
    [SPYCE]: {
        'image': require('@/assets/images/items/drug/drug_5.jpg')
    },
    [NEWKE]: {
        'image': require('@/assets/images/items/drug/drug_6.jpg')
    },
    [PINQ]: {
        'image': require('@/assets/images/items/drug/drug_7.jpg')
    },
    [BACTA]: {
        'image': require('@/assets/images/items/drug/drug_8.jpg')
    },
    [BETAPROPYL]: {
        'image': require('@/assets/images/items/drug/drug_9.jpg')
    },
    [PYMP]: {
        'image': require('@/assets/images/items/drug/drug_10.jpg')
    },
    [ROSEBUD]: {
        'image': require('@/assets/images/items/drug/drug_11.jpg')
    },

    [BANANA_TREE]: {
        'image': require('@/assets/images/items/plant/fruit_tree00.jpg')
    },
    [BANANA]: {
        'image': require('@/assets/images/items/fruit/fruit00.jpg')
    },
    [CREEPNUT_TREE]: {
        'image': require('@/assets/images/items/plant/fruit_tree01.jpg')
    },
    [CREEPNUT]: {
        'image': require('@/assets/images/items/fruit/fruit01.jpg')
    },
    [CACTAX]: {
        'image': require('@/assets/images/items/plant/fruit_tree02.jpg')
    },
    [MEZTINE]: {
        'image': require('@/assets/images/items/fruit/fruit02.jpg')
    },
    [BIFFLON]: {
        'image': require('@/assets/images/items/plant/fruit_tree03.jpg')
    },
    [GUNTIFLOP]: {
        'image': require('@/assets/images/items/fruit/fruit03.jpg')
    },
    [PULMMINAGRO]: {
        'image': require('@/assets/images/items/plant/fruit_tree04.jpg')
    },
    [PLOSHMINA]: {
        'image': require('@/assets/images/items/fruit/fruit04.jpg')
    },
    [PRECATUS]: {
        'image': require('@/assets/images/items/plant/fruit_tree05.jpg')
    },
    [PRECATI]: {
        'image': require('@/assets/images/items/fruit/fruit05.jpg')
    },
    [BUTTALIEN]: {
        'image': require('@/assets/images/items/plant/fruit_tree06.jpg')
    },
    [BOTTINE]: {
        'image': require('@/assets/images/items/fruit/fruit06.jpg')
    },
    [PLATACIA]: {
        'image': require('@/assets/images/items/plant/fruit_tree07.jpg')
    },
    [FRAGILANE]: {
        'image': require('@/assets/images/items/fruit/fruit07.jpg')
    },
    [TUBILISCUS]: {
        'image': require('@/assets/images/items/plant/fruit_tree08.jpg')
    },
    [ANEMOLE]: {
        'image': require('@/assets/images/items/fruit/fruit08.jpg')
    },
    [GRAAPSHOOT]: {
        'image': require('@/assets/images/items/plant/fruit_tree09.jpg')
    },
    [PENICRAFT]: {
        'image': require('@/assets/images/items/fruit/fruit09.jpg')
    },
    [FIBONICCUS]: {
        'image': require('@/assets/images/items/plant/fruit_tree10.jpg')
    },
    [KUBINUS]: {
        'image': require('@/assets/images/items/fruit/fruit10.jpg')
    },
    [MYCOPIA]: {
        'image': require('@/assets/images/items/plant/fruit_tree11.jpg')
    },
    [CALEBOOT]: {
        'image': require('@/assets/images/items/fruit/fruit11.jpg')
    },
    [ASPERAGUNK]: {
        'image': require('@/assets/images/items/plant/fruit_tree12.jpg')
    },
    [FILANDRA]: {
        'image': require('@/assets/images/items/fruit/fruit12.jpg')
    },
    [BUMPJUMPKIN]: {
        'image': require('@/assets/images/items/plant/fruit_tree13.jpg')
    },
    [JUMPKIN]: {
        'image': require('@/assets/images/items/fruit/fruit13.jpg')
    },

    [PLASTENITE_ARMOR]: {
        'image': require('@/assets/images/items/plastenite_armor.jpg')
    },
    [CAMERA_ITEM]: {
        'image': require('@/assets/images/items/camera.jpg')
    },
    [EXTINGUISHER]: {
        'image': require('@/assets/images/items/extinguisher.jpg')
    },
    [DUCT_TAPE]: {
        'image': require('@/assets/images/items/duck_tape.jpg')
    },
    [BLASTER]: {
        'image': require('@/assets/images/items/blaster.jpg')
    },
    [HACKER_KIT]: {
        'image': require('@/assets/images/items/hacker_kit.jpg')
    },
    [GRENADE]: {
        'image': require('@/assets/images/items/grenade.jpg')
    },
    [QUADRIMETRIC_COMPASS]: {
        'image': require('@/assets/images/items/quad_compass.jpg')
    },
    [ADJUSTABLE_WRENCH]: {
        'image': require('@/assets/images/items/wrench.jpg')
    },
    [APRON]: {
        'image': require('@/assets/images/items/apron.jpg')
    },
    [BLOCK_POST_IT]: {
        'image': require('@/assets/images/items/postit_bloc.jpg')
    },
    [POST_IT]: {
        'image': require('@/assets/images/items/postit.jpg')
    },
    [ROPE]: {
        'image': require('@/assets/images/items/rope.jpg')
    },
    [DRILL]: {
        'image': require('@/assets/images/items/driller.jpg')
    },
    [KNIFE]: {
        'image': require('@/assets/images/items/knife.jpg')
    },
    [GLOVES]: {
        'image': require('@/assets/images/items/protection_gloves.jpg')
    },
    [SOAP]: {
        'image': require('@/assets/images/items/soap.jpg')
    },
    [TABULATRIX]: {
        'image': require('@/assets/images/items/printer.jpg')
    },
    [OXYGEN_CAPSULE]: {
        'image': require('@/assets/images/items/oxy_capsule.jpg')
    },
    [FUEL_CAPSULE]: {
        'image': require('@/assets/images/items/fuel_capsule.jpg')
    },
    [THICK_TUBE]: {
        'image': require('@/assets/images/items/thick_tube.jpg')
    },
    [SPACE_CAPSULE]: {
        'image': require('@/assets/images/items/space_capsule.jpg')
    },
    [SPORE_SUCKER]: {
        'image': require('@/assets/images/items/spore_sucker.jpg')
    },
    [ALIEN_HOLOGRAPHIC_TV]: {
        'image': require('@/assets/images/items/alien_holographic_tv.jpg')
    },
    [WALKIE_TALKIE]: {
        'image': require('@/assets/images/items/walkie_talkie.jpg')
    },
    [TRACKER]: {
        'image': require('@/assets/images/items/tracker.jpg')
    },
    [ITRACKIE]: {
        'image': require('@/assets/images/items/super_talkie.jpg')
    },
    [ORGANIC_WASTE]: {
        'image': require('@/assets/images/items/organic_waste.jpg')
    },
    [SPACESUIT]: {
        'image': require('@/assets/images/items/space_suit.jpg')
    },
    [MAGELLAN_LIQUID_MAP]: {
        'image': require('@/assets/images/items/magellan_liquid_map.jpg')
    },
    [STARMAP_FRAGMENT]: {
        'image': require('@/assets/images/items/super_map.jpg')
    },
    [WATER_STICK]: {
        'image': require('@/assets/images/items/water_stick.jpg')
    },
    [INVERTEBRATE_SHELL]: {
        'image': require('@/assets/images/items/insectoid_shell.jpg')
    },
    [PRINTED_CIRCUIT_JELLY]: {
        'image': require('@/assets/images/items/computer_jelly.jpg')
    },
    [OLD_FAITHFUL]: {
        'image': require('@/assets/images/items/machine_gun.jpg')
    },
    [ROCKET_LAUNCHER]: {
        'image': require('@/assets/images/items/missile_launcher.jpg')
    },
    [LIZARO_JUNGLE]: {
        'image': require('@/assets/images/items/sniper_riffle.jpg')
    },
    [NATAMY_RIFLE]: {
        'image': require('@/assets/images/items/natamy_riffle.jpg')
    },
    [ALIEN_STEAK]: {
        'image': require('@/assets/images/items/ration_5.jpg')
    },
};
