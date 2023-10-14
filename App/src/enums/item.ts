const COFFEE = "coffee";
const PLASTIC_SCRAP = "plastic_scraps";
const METAL_SCRAP = "metal_scraps";
const ANTIGRAV_SCOOTER = "antigrav_scooter";
const MEDIKIT = "medikit";
const OSCILLOSCOPE = 'oscilloscope';
const ROLLING_BOULDER = 'rolling_boulder';
const ALIEN_BOTTLE_OPENER = 'alien_bottle_opener';
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
const JUNKIN = 'junkin';

const CREEPNUT_TREE = "creepist";
const BANANA_TREE = "banana_tree";
const CACTAX = 'cactax';
const BIFFLON = 'bifflon';
const PULMMINAGRO = 'pulminagro';
const PRECATUS = 'recatus';
const BUTTALIEN = 'buttalien';
const PLATACIA = 'platacia';
const TUBILISCUS = 'tubiliscus';
const GRAAPSHOOT = 'graapshoot';
const FIBONICCUS = 'Fiboniccus';
const MYCOPIA = 'mycopia';
const ASPERAGUNK = 'asperagunk';
const BUMPJUNKIN = 'bumpjunkin';

const BLUEPRINT = "blueprint";
const SNIPER_HELMET = "sniper_helmet";
const APPRENTON = "apprenton";
const STANDARD_RATION = "standard_ration";
const COOKED_RATION = "cooked_ration";
const MAD_KUBE = "mad_kube";
const MICROWAVE = "microwave";
const SUPERFREEZER = "superfreezer";
const HYDROPOT = "hydropot";
const PLASTENITE_ARMOR = "plastenite_armor";
const CAMERA_ITEM = "camera_item";
const EXTINGUISHER = "extinguisher";
const DUCT_TAPE = "duct_tape";
const BLASTER = "blaster";
const HACKER_KIT = "hacker_kit";
const GRENADE = "grenade";
const QUADRIMETRIC_COMPASS = "quadrimetric_compass";
const ADJUSTABLE_WRENCH = "adjustable_wrench";
const APRON = "stainproof_apron";
const BLOCK_POST_IT = "block_of_post_it";
const ROPE = "rope";
const DRILL = "drill";
const KNIFE = "knife";
const GLOVES = "protective_gloves";
const SOAP = "soap";
const TABULATRIX = "tabulatrix";
const THICK_TUBE = "thick_tube";
const OXYGEN_CAPSULE = "oxygen_capsule";
const FUEL_CAPSULE = "fuel_capsule";
const SPACE_CAPSULE = "space_capsule";
const JAR_OF_ALIEN_OIL = "jar_of_alien_oil";
const BANDAGE = "bandage";
const SPORE_SUCKER = "spore_sucker";
const ALIEN_HOLOGRAPHIC_TV = "alien_holographic_tv";
const ORGANIC_WASTE = "organic_waste";
const SPACESUIT = "spacesuit";

const ITRACKIE = "itrackie";
const TRACKER = "tracker";
const WALKIE_TALKIE = "walkie_talkie";


export const itemEnum: {[index: string]: any} = {
    [ALIEN_BOTTLE_OPENER]: {
        'image': 'src/assets/images/items/alien_can_opener.jpg'
    },
    [ECHOLOCATOR]: {
        'image': 'src/assets/images/items/echo_sounder.jpg'
    },
    [ROLLING_BOULDER]: {
        'image': 'src/assets/images/items/rolling_boulder.jpg'
    },
    [OSCILLOSCOPE]: {
        'image': 'src/assets/images/items/wavoscope.jpg'
    },
    [MEDIKIT]: {
        'image': 'src/assets/images/items/medikit.jpg'
    },
    [BANDAGE]: {
        'image': 'src/assets/images/items/bandage.jpg'
    },
    [ANTIGRAV_SCOOTER]: {
        'image': 'src/assets/images/items/antigrav_scooter.jpg'
    },
    [JAR_OF_ALIEN_OIL]: {
        'image': 'src/assets/images/items/alien_oil.jpg'
    },
    [COFFEE]: {
        'image': 'src/assets/images/items/coffee.jpg'
    },
    [METAL_SCRAP]: {
        'image': 'src/assets/images/items/metal_scraps.jpg'
    },
    [PLASTIC_SCRAP]: {
        'image': 'src/assets/images/items/plastic_scraps.jpg'
    },
    [APPRENTON]: {
        'image': 'src/assets/images/items/book.jpg'
    },
    [BLUEPRINT]: {
        'image': 'src/assets/images/items/blueprint.jpg'
    },
    [SNIPER_HELMET]: {
        'image': 'src/assets/images/items/aiming_helmet.jpg'
    },
    [STANDARD_RATION]: {
        'image': 'src/assets/images/items/ration_0.jpg'
    },
    [COOKED_RATION]: {
        'image': 'src/assets/images/items/ration_1.jpg'
    },
    [MAD_KUBE]: {
        'image': 'src/assets/images/items/mad_kube.jpg'
    },
    [MICROWAVE]: {
        'image': 'src/assets/images/items/microwave.jpg'
    },
    [SUPERFREEZER]: {
        'image': 'src/assets/images/items/freezer.jpg'
    },
    [HYDROPOT]: {
        'image': 'src/assets/images/items/tree_pot.jpg'
    },

    [TWINOID]: {
        'image': 'src/assets/images/items/drug/drug_0.jpg'
    },
    [XENOX]: {
        'image': 'src/assets/images/items/drug/drug_1.jpg'
    },
    [PHUXX]: {
        'image': 'src/assets/images/items/drug/drug_2.jpg'
    },
    [EUFURYLATE]: {
        'image': 'src/assets/images/items/drug/drug_3.jpg'
    },
    [SOMA]: {
        'image': 'src/assets/images/items/drug/drug_4.jpg'
    },
    [SPYCE]: {
        'image': 'src/assets/images/items/drug/drug_5.jpg'
    },
    [NEWKE]: {
        'image': 'src/assets/images/items/drug/drug_6.jpg'
    },
    [PINQ]: {
        'image': 'src/assets/images/items/drug/drug_7.jpg'
    },
    [BACTA]: {
        'image': 'src/assets/images/items/drug/drug_8.jpg'
    },
    [BETAPROPYL]: {
        'image': 'src/assets/images/items/drug/drug_9.jpg'
    },
    [PYMP]: {
        'image': 'src/assets/images/items/drug/drug_10.jpg'
    },
    [ROSEBUD]: {
        'image': 'src/assets/images/items/drug/drug_11.jpg'
    },

    [BANANA_TREE]: {
        'image': 'src/assets/images/items/plant/fruit_tree00.jpg'
    },
    [BANANA]: {
        'image': 'src/assets/images/items/fruit/fruit00.jpg'
    },
    [CREEPNUT_TREE]: {
        'image': 'src/assets/images/items/plant/fruit_tree01.jpg'
    },
    [CREEPNUT]: {
        'image': 'src/assets/images/items/fruit/fruit01.jpg'
    },
    [CACTAX]: {
        'image': 'src/assets/images/items/plant/fruit_tree02.jpg'
    },
    [MEZTINE]: {
        'image': 'src/assets/images/items/fruit/fruit02.jpg'
    },
    [BIFFLON]: {
        'image': 'src/assets/images/items/plant/fruit_tree03.jpg'
    },
    [GUNTIFLOP]: {
        'image': 'src/assets/images/items/fruit/fruit03.jpg'
    },
    [PULMMINAGRO]: {
        'image': 'src/assets/images/items/plant/fruit_tree04.jpg'
    },
    [PLOSHMINA]: {
        'image': 'src/assets/images/items/fruit/fruit04.jpg'
    },
    [PRECATUS]: {
        'image': 'src/assets/images/items/plant/fruit_tree05.jpg'
    },
    [PRECATI]: {
        'image': 'src/assets/images/items/fruit/fruit05.jpg'
    },
    [BUTTALIEN]: {
        'image': 'src/assets/images/items/plant/fruit_tree06.jpg'
    },
    [BOTTINE]: {
        'image': 'src/assets/images/items/fruit/fruit06.jpg'
    },
    [PLATACIA]: {
        'image': 'src/assets/images/items/plant/fruit_tree07.jpg'
    },
    [FRAGILANE]: {
        'image': 'src/assets/images/items/fruit/fruit07.jpg'
    },
    [TUBILISCUS]: {
        'image': 'src/assets/images/items/plant/fruit_tree08.jpg'
    },
    [ANEMOLE]: {
        'image': 'src/assets/images/items/fruit/fruit08.jpg'
    },
    [GRAAPSHOOT]: {
        'image': 'src/assets/images/items/plant/fruit_tree09.jpg'
    },
    [PENICRAFT]: {
        'image': 'src/assets/images/items/fruit/fruit09.jpg'
    },
    [FIBONICCUS]: {
        'image': 'src/assets/images/items/plant/fruit_tree10.jpg'
    },
    [KUBINUS]: {
        'image': 'src/assets/images/items/fruit/fruit10.jpg'
    },
    [MYCOPIA]: {
        'image': 'src/assets/images/items/plant/fruit_tree11.jpg'
    },
    [CALEBOOT]: {
        'image': 'src/assets/images/items/fruit/fruit11.jpg'
    },
    [ASPERAGUNK]: {
        'image': 'src/assets/images/items/plant/fruit_tree12.jpg'
    },
    [FILANDRA]: {
        'image': 'src/assets/images/items/fruit/fruit12.jpg'
    },
    [BUMPJUNKIN]: {
        'image': 'src/assets/images/items/plant/fruit_tree13.jpg'
    },
    [JUNKIN]: {
        'image': 'src/assets/images/items/fruit/fruit13.jpg'
    },

    [PLASTENITE_ARMOR]: {
        'image': 'src/assets/images/items/plastenite_armor.jpg'
    },
    [CAMERA_ITEM]: {
        'image': 'src/assets/images/items/camera.jpg'
    },
    [EXTINGUISHER]: {
        'image': 'src/assets/images/items/extinguisher.jpg'
    },
    [DUCT_TAPE]: {
        'image': 'src/assets/images/items/duck_tape.jpg'
    },
    [BLASTER]: {
        'image': 'src/assets/images/items/blaster.jpg'
    },
    [HACKER_KIT]: {
        'image': 'src/assets/images/items/hacker_kit.jpg'
    },
    [GRENADE]: {
        'image': 'src/assets/images/items/grenade.jpg'
    },
    [QUADRIMETRIC_COMPASS]: {
        'image': 'src/assets/images/items/quad_compass.jpg'
    },
    [ADJUSTABLE_WRENCH]: {
        'image': 'src/assets/images/items/wrench.jpg'
    },
    [APRON]: {
        'image': 'src/assets/images/items/apron.jpg'
    },
    [BLOCK_POST_IT]: {
        'image': 'src/assets/images/items/postit_bloc.jpg'
    },
    [ROPE]: {
        'image': 'src/assets/images/items/rope.jpg'
    },
    [DRILL]: {
        'image': 'src/assets/images/items/driller.jpg'
    },
    [KNIFE]: {
        'image': 'src/assets/images/items/knife.jpg'
    },
    [GLOVES]: {
        'image': 'src/assets/images/items/protection_gloves.jpg'
    },
    [SOAP]: {
        'image': 'src/assets/images/items/soap.jpg'
    },
    [TABULATRIX]: {
        'image': 'src/assets/images/items/printer.jpg'
    },
    [OXYGEN_CAPSULE]: {
        'image': 'src/assets/images/items/oxy_capsule.jpg'
    },
    [FUEL_CAPSULE]: {
        'image': 'src/assets/images/items/fuel_capsule.jpg'
    },
    [THICK_TUBE]: {
        'image': 'src/assets/images/items/thick_tube.jpg'
    },
    [SPACE_CAPSULE]: {
        'image': 'src/assets/images/items/space_capsule.jpg'
    },
    [SPORE_SUCKER]: {
        'image': 'src/assets/images/items/spore_sucker.jpg'
    },
    [ALIEN_HOLOGRAPHIC_TV]: {
        'image': 'src/assets/images/items/alien_holographic_tv.jpg'
    },
    [WALKIE_TALKIE]: {
        'image': 'src/assets/images/items/walkie_talkie.jpg'
    },
    [TRACKER]: {
        'image': 'src/assets/images/items/tracker.jpg'
    },
    [ITRACKIE]: {
        'image': 'src/assets/images/items/super_talkie.jpg'
    },
    [ORGANIC_WASTE]: {
        'image': 'src/assets/images/items/organic_waste.jpg'
    },
    [SPACESUIT]: {
        'image': 'src/assets/images/items/space_suit.jpg'
    },
}
;
