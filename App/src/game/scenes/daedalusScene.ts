import Phaser from 'phaser';
import { Room } from "@/entities/Room";

import background from '@/game/assets/tilemaps/background.png';
import planet_0 from '@/game/assets/tilemaps/planet_0.png';
import planet_1 from '@/game/assets/tilemaps/planet_1.png';
import planet_2 from '@/game/assets/tilemaps/planet_2.png';
import planet_3 from '@/game/assets/tilemaps/planet_3.png';
import planet_4 from '@/game/assets/tilemaps/planet_4.png';
import door_ground_tileset from "@/game/assets/tilemaps/door_ground_tileset.png";
import ground_tileset from "@/game/assets/tilemaps/ground_tileset.png";
import wall_tileset from "@/game/assets/tilemaps/wall_tileset.png";
import beds_object from "@/game/assets/tilemaps/beds_object.png";
import surgery_object from "@/game/assets/tilemaps/surgery_console_object.png";
import chair_object from "@/game/assets/tilemaps/chair_object.png";
import door_object from "@/game/assets/tilemaps/door_object.png";
import neron_object from "@/game/assets/tilemaps/neron_terminal_object.png";
import shelf_object from "@/game/assets/tilemaps/shelf_object.png";
import tube_object from "@/game/assets/tilemaps/tube_object.png";
import distiller_object from "@/game/assets/tilemaps/distiller_object.png";
import camera_object from "@/game/assets/tilemaps/camera_object.png";
import cryomodule_object from "@/game/assets/tilemaps/cryomodule_object.png";
import gravity_object from "@/game/assets/tilemaps/gravity_object.png";
import mycoscan_object from "@/game/assets/tilemaps/mycoscan_object.png";
import laboratory_object from "@/game/assets/tilemaps/laboratory_object.png";
import mural_shelf_object from "@/game/assets/tilemaps/mural_shelf.png";
import centrifuge_object from "@/game/assets/tilemaps/centrifuge_object.png";
import wall_box from "@/game/assets/tilemaps/wall_box.png";
import paper_dispenser from "@/game/assets/tilemaps/paper_dispenser.png";
import desk_object from "@/game/assets/tilemaps/desk_object.png";
import shelf_front_storage1 from "@/game/assets/tilemaps/shelf_front_storage1.png";
import shelf_front_storage2 from "@/game/assets/tilemaps/shelf_front_storage2.png";
import shelf_front_storage3 from "@/game/assets/tilemaps/shelf_front_storage3.png";
import shelf_front_storage4 from "@/game/assets/tilemaps/shelf_front_storage4.png";
import papers from "@/game/assets/tilemaps/papers.png";
import broom from "@/game/assets/tilemaps/broom.png";
import transparent_wall_object from "@/game/assets/tilemaps/transparent_wall_object.png";
import puddle from "@/game/assets/tilemaps/puddle.png";
import shower from "@/game/assets/tilemaps/shower.png";
import washRoom1 from "@/game/assets/tilemaps/washRoom1.png";
import washRoom2 from "@/game/assets/tilemaps/washRoom2.png";
import towelRack from "@/game/assets/tilemaps/towelRack.png";
import slippers from "@/game/assets/tilemaps/slippers.png";
import poster from "@/game/assets/tilemaps/poster.png";
import garden_equipment from "@/game/assets/tilemaps/garden_equipment.png";
import garden_console from "@/game/assets/tilemaps/garden_console.png";
import garden_engine from "@/game/assets/tilemaps/garden_engine.png";
import pneumatic_distributor from "@/game/assets/tilemaps/pneumatic_distributor.png";
import kitchen_1 from "@/game/assets/tilemaps/kitchen_1.png";
import kitchen_2 from "@/game/assets/tilemaps/kitchen_2.png";
import pneumatic_distributor_2 from "@/game/assets/tilemaps/pneumatic_distributor_2.png";
import coffee_machine from "@/game/assets/tilemaps/coffee_machine.png";
import table from "@/game/assets/tilemaps/table.png";
import oxygen_tank from "@/game/assets/tilemaps/oxygen_tank.png";
import shelf_center_alpha_storage_1 from "@/game/assets/tilemaps/shelf_center_alpha_storage_1.png";
import shelf_center_alpha_storage_2 from "@/game/assets/tilemaps/shelf_center_alpha_storage_2.png";
import shelf_center_bravo_storage_1 from "@/game/assets/tilemaps/shelf_center_bravo_storage_1.png";
import shelf_center_bravo_storage_2 from "@/game/assets/tilemaps/shelf_center_bravo_storage_2.png";
import shelf_center_bravo_storage_3 from "@/game/assets/tilemaps/shelf_center_bravo_storage_3.png";
import neron_core from "@/game/assets/tilemaps/neron_core.png";
import nexus_lamp from "@/game/assets/tilemaps/nexus_lamp.png";
import bios_terminal_calculator from "@/game/assets/tilemaps/bios_terminal_calculator.png";
import fuel_tank from "@/game/assets/tilemaps/fuel_tank.png";
import board from "@/game/assets/tilemaps/board.png";
import shelf_rear_alpha_storage from "@/game/assets/tilemaps/shelf_rear_alpha_storage.png";
import shelf_rear_bravo_storage from "@/game/assets/tilemaps/shelf_rear_bravo_storage.png";
import workshop from "@/game/assets/tilemaps/workshop.png";
import worktable from "@/game/assets/tilemaps/worktable.png";
import garden_engine_anim from "@/game/assets/tilemaps/garden_engine_anim.png";
import patrol_ship from "@/game/assets/tilemaps/patrol_ship.png";
import small_takeoff_platform from "@/game/assets/tilemaps/small_takeoff_platform.png";
import bay_door from "@/game/assets/tilemaps/bay_door.png";
import yellow_lamp from "@/game/assets/tilemaps/yellow_lamp.png";
import jukebox from "@/game/assets/tilemaps/jukebox.png";
import floor_lamp from "@/game/assets/tilemaps/floor_lamp.png";
import magnetic_net from "@/game/assets/tilemaps/magnetic_net.png";
import pasiphae from "@/game/assets/tilemaps/pasiphae.png";
import dynarcade from "@/game/assets/tilemaps/dynarcade.png";
import bay from "@/game/assets/tilemaps/bay.png";
import icarus_wall from "@/game/assets/tilemaps/icarus_wall.png";
import icarus_access from "@/game/assets/tilemaps/icarus_access.png";
import takeoff_platform from "@/game/assets/tilemaps/takeoff_platform.png";
import turret_tags from "@/game/assets/tilemaps/turret_tags.png";
import turret_back_bravo from "@/game/assets/tilemaps/turret_back_bravo.png";
import turret_back_alpha from "@/game/assets/tilemaps/turret_back_alpha.png";
import alpha_turret_front from "@/game/assets/tilemaps/alpha_turret_front.png";
import bravo_turret_front from "@/game/assets/tilemaps/bravo_turret_front.png";
import structure from "@/game/assets/tilemaps/structure.png";
import magnetic_return from "@/game/assets/tilemaps/magnetic_return.png";
import turret_ground from "@/game/assets/tilemaps/turret_ground.png";
import aeration_grid from "@/game/assets/tilemaps/aeration_grid.png";
import comms_center from "@/game/assets/tilemaps/comms_center.png";
import astro_terminal from "@/game/assets/tilemaps/astro_terminal.png";
import astro_terminal2 from "@/game/assets/tilemaps/astro_terminal2.png";
import cockpit_window from "@/game/assets/tilemaps/cockpit_window.png";
import command_board from "@/game/assets/tilemaps/command_board.png";
import command_terminal from "@/game/assets/tilemaps/command_terminal.png";
import comms_center2 from "@/game/assets/tilemaps/comms_center2.png";
import floor_lamp_bridge from "@/game/assets/tilemaps/floor_lamp_bridge.png";
import semi_circle_lamp from "@/game/assets/tilemaps/semi_circle_lamp.png";
import semicircle_floor from "@/game/assets/tilemaps/semicircle_floor.png";
import scanner from "@/game/assets/tilemaps/scanner.png";
import antenna from "@/game/assets/tilemaps/antenna.png";
import combustion_chamber from "@/game/assets/tilemaps/combustion_chamber.png";
import engine_room1 from "@/game/assets/tilemaps/engine_room1.png";
import engine_room2 from "@/game/assets/tilemaps/engine_room2.png";
import paraboles from "@/game/assets/tilemaps/paraboles.png";
import pilgred from "@/game/assets/tilemaps/pilgred.png";
import planet_scanner from "@/game/assets/tilemaps/planet_scanner.png";
import quantum_sensors from "@/game/assets/tilemaps/quantum_sensors.png";
import reactor from "@/game/assets/tilemaps/reactor.png";
import terminal_pilgred from "@/game/assets/tilemaps/terminal_pilgred.png";
import sofa_asset from "@/game/assets/tilemaps/sofa_asset.png";
import small_sofa from "@/game/assets/tilemaps/small_sofa.png";

import character from "@/game/assets/images/characters.png";
import characterFrame from "@/game/assets/images/characters.json";
import CharacterObject from "@/game/objects/characterObject";
import InteractObject from "@/game/objects/interactObject";

import laboratory from "@/game/assets/mush_lab.json";
import medlab from "@/game/assets/mush_medlab.json";
import central_corridor from "@/game/assets/center_corridor.json";
import front_storage from "@/game/assets/front_storage.json";
import front_corridor from "@/game/assets/front_corridor.json";
import alpha_dorm from "@/game/assets/alpha_dorm.json";
import bravo_dorm from "@/game/assets/bravo_dorm.json";
import hydroponic_garden from "@/game/assets/garden.json";
import refectory from "@/game/assets/refectory.json";
import center_alpha_storage from "@/game/assets/center_alpha_storage.json";
import center_bravo_storage from "@/game/assets/center_bravo_storage.json";
import rear_corridor from "@/game/assets/rear_corridor.json";
import nexus from "@/game/assets/nexus.json";
import rear_bravo_storage from "@/game/assets/rear_bravo_storage.json";
import rear_alpha_storage from "@/game/assets/rear_alpha_storage.json";
import alpha_bay_2 from "@/game/assets/alpha_bay_2.json";
import alpha_bay from "@/game/assets/bay_alpha.json";
import bravo_bay from "@/game/assets/bravo_bay.json";
import icarus_bay from "@/game/assets/bay_icarus.json";
import front_bravo_turret from "@/game/assets/front_bravo_turret.json";
import centre_bravo_turret from "@/game/assets/center_bravo_turret.json";
import rear_bravo_turret from "@/game/assets/rear_bravo_turret.json";
import front_alpha_turret from "@/game/assets/front_alpha_turret.json";
import centre_alpha_turret from "@/game/assets/center_alpha_turret.json";
import rear_alpha_turret from "@/game/assets/rear_alpha_turret.json";
import bridge from "@/game/assets/bridge.json";
import engine_room from "@/game/assets/engine_room.json";
import patrol_ship_bravo_epicure from "@/game/assets/patrol_ship_bravo_epicure.json";
import patrol_ship_bravo_planton from "@/game/assets/patrol_ship_bravo_planton.json";
import patrol_ship_bravo_socrate from "@/game/assets/patrol_ship_bravo_socrate.json";
import patrol_ship_alpha_tamarin from "@/game/assets/patrol_ship_alpha_tamarin.json";
import patrol_ship_alpha_jujube from "@/game/assets/patrol_ship_alpha_jujube.json";
import patrol_ship_alpha_longane from "@/game/assets/patrol_ship_alpha_longane.json";
import patrol_ship_alpha_2_wallis from "@/game/assets/patrol_ship_alpha_2_wallis.json";
import pasiphae_asset from "@/game/assets/pasiphae.json";

import fire_particles_frame from "@/game/assets/images/fire_particles.json";
import fire_particles from "@/game/assets/images/fire_particles.png";
import smoke_particle from "@/game/assets/images/smoke_particle.png";
import tile_highlight from "@/game/assets/images/tile_highlight.png";
import hunter from "@/game/assets/images/hunter.png";

import OutlinePostFx from 'phaser3-rex-plugins/plugins/outlinepipeline.js';

import { Player } from "@/entities/Player";
import PlayableCharacterObject from "@/game/objects/playableCharacterObject";
import { IsometricCoordinates, CartesianCoordinates } from "@/game/types";
import IsometricGeom from "@/game/scenes/isometricGeom";
import { SceneGrid } from "@/game/scenes/sceneGrid";
import { NavMeshGrid } from "@/game/scenes/navigationGrid";
import store from "@/store";
import MushTiledMap from "@/game/tiled/mushTiledMap";
import EquipmentObject from "@/game/objects/equipmentObject";
import { Equipment } from "@/entities/Equipment";
import DecorationObject from "@/game/objects/decorationObject";
import DoorObject from "@/game/objects/doorObject";
import DoorGroundObject from "@/game/objects/doorGroundObject";
import { Door } from "@/entities/Door";
import DeathZone = Phaser.GameObjects.Particles.Zones.DeathZone;
import { Planet } from "@/entities/Planet";


export default class DaedalusScene extends Phaser.Scene
{
    private characterSize = 6;
    private readonly isoTileSize: number;
    private sceneIsoSize: IsometricCoordinates;
    private readonly playerIsoSize: IsometricCoordinates;

    public playerSprite! : PlayableCharacterObject;

    private player : Player;
    public room : Room;
    private equipments : Array<EquipmentObject>;
    private map: MushTiledMap | null;
    private targetHighlightObject?: Phaser.GameObjects.Sprite;

    public sceneGrid: SceneGrid;
    public navMeshGrid: NavMeshGrid;
    private roomBasicSceneGrid: SceneGrid;

    private isScreenSliding = { x: false, y: false };
    private cameraTarget: CartesianCoordinates = new CartesianCoordinates(0,0);
    private cameraDirection: CartesianCoordinates = new CartesianCoordinates(0,0);
    private previousRoom: string | undefined = undefined;

    public selectedGameObject : Phaser.GameObjects.GameObject | null;
    private fireParticles: Array<Phaser.GameObjects.Particles.ParticleEmitter> = [];
    private starParticles: Array<Phaser.GameObjects.Particles.ParticleEmitter> = [];
    private hunterParticle: Phaser.GameObjects.Particles.ParticleEmitter | null = null;
    private background: Phaser.GameObjects.TileSprite | undefined;
    private isTravelling= false;
    private attackingHunters = 0;

    constructor(player: Player) {
        super('game-scene');

        this.isoTileSize = 16;
        this.sceneIsoSize= new IsometricCoordinates(0, 0);
        this.playerIsoSize = new IsometricCoordinates(this.characterSize, this.characterSize);


        if (player.room === null){
            throw new Error('player should have a room');
        }

        this.room = player.room;
        this.map = null;
        this.player = player;
        this.equipments = [];

        this.sceneGrid = new SceneGrid(this, this.characterSize);
        this.roomBasicSceneGrid = new SceneGrid(this, this.characterSize);
        this.navMeshGrid = new NavMeshGrid(this);

        this.selectedGameObject = null;
    }

    preload(): void
    {
        this.load.tilemapTiledJSON('medlab', medlab);
        this.load.tilemapTiledJSON('laboratory', laboratory);
        this.load.tilemapTiledJSON('central_corridor', central_corridor);
        this.load.tilemapTiledJSON('front_storage', front_storage);
        this.load.tilemapTiledJSON('front_corridor', front_corridor);
        this.load.tilemapTiledJSON('bravo_dorm', bravo_dorm);
        this.load.tilemapTiledJSON('alpha_dorm', alpha_dorm);
        this.load.tilemapTiledJSON('hydroponic_garden', hydroponic_garden);
        this.load.tilemapTiledJSON('refectory', refectory);
        this.load.tilemapTiledJSON('center_alpha_storage', center_alpha_storage);
        this.load.tilemapTiledJSON('center_bravo_storage', center_bravo_storage);
        this.load.tilemapTiledJSON('rear_corridor', rear_corridor);
        this.load.tilemapTiledJSON('nexus', nexus);
        this.load.tilemapTiledJSON('rear_bravo_storage', rear_bravo_storage);
        this.load.tilemapTiledJSON('rear_alpha_storage', rear_alpha_storage);
        this.load.tilemapTiledJSON('alpha_bay_2', alpha_bay_2);
        this.load.tilemapTiledJSON('alpha_bay', alpha_bay);
        this.load.tilemapTiledJSON('bravo_bay', bravo_bay);
        this.load.tilemapTiledJSON('icarus_bay', icarus_bay);
        this.load.tilemapTiledJSON('front_bravo_turret', front_bravo_turret);
        this.load.tilemapTiledJSON('centre_bravo_turret', centre_bravo_turret);
        this.load.tilemapTiledJSON('rear_bravo_turret', rear_bravo_turret);
        this.load.tilemapTiledJSON('front_alpha_turret', front_alpha_turret);
        this.load.tilemapTiledJSON('centre_alpha_turret', centre_alpha_turret);
        this.load.tilemapTiledJSON('rear_alpha_turret', rear_alpha_turret);
        this.load.tilemapTiledJSON('bridge', bridge);
        this.load.tilemapTiledJSON('engine_room', engine_room);
        this.load.tilemapTiledJSON('patrol_ship_bravo_epicure', patrol_ship_bravo_epicure);
        this.load.tilemapTiledJSON('patrol_ship_bravo_planton', patrol_ship_bravo_planton);
        this.load.tilemapTiledJSON('patrol_ship_bravo_socrate', patrol_ship_bravo_socrate);
        this.load.tilemapTiledJSON('patrol_ship_alpha_jujube', patrol_ship_alpha_jujube);
        this.load.tilemapTiledJSON('patrol_ship_alpha_tamarin', patrol_ship_alpha_tamarin);
        this.load.tilemapTiledJSON('patrol_ship_alpha_longane', patrol_ship_alpha_longane);
        this.load.tilemapTiledJSON('patrol_ship_alpha_2_wallis', patrol_ship_alpha_2_wallis);
        this.load.tilemapTiledJSON('pasiphae', pasiphae_asset);

        this.load.image('ground_tileset', ground_tileset);
        this.load.image('wall_tileset', wall_tileset);
        this.load.image('planet_0', planet_0);
        this.load.image('planet_1', planet_1);
        this.load.image('planet_2', planet_2);
        this.load.image('planet_3', planet_3);
        this.load.image('planet_4', planet_4);
        this.load.image('background', background);

        this.load.atlas('character', character, characterFrame);

        this.load.spritesheet('centrifuge_object', centrifuge_object, { frameWidth: 30, frameHeight: 34 });
        this.load.spritesheet('desk_object', desk_object, { frameWidth: 45, frameHeight: 37 });
        this.load.spritesheet('paper_dispenser', paper_dispenser, { frameWidth: 9, frameHeight: 15 });
        this.load.spritesheet('laboratory_object', laboratory_object, { frameWidth: 79, frameHeight: 57 });
        this.load.spritesheet('mural_shelf', mural_shelf_object, { frameWidth: 46, frameHeight: 28 });
        this.load.spritesheet('mycoscan_object', mycoscan_object, { frameWidth: 81, frameHeight: 57 });

        this.load.spritesheet('gravity_object', gravity_object, { frameWidth: 28, frameHeight: 46 });
        this.load.spritesheet('wall_box', wall_box, { frameWidth: 14, frameHeight: 15 });
        this.load.spritesheet('cryomodule_object', cryomodule_object, { frameWidth: 128, frameHeight: 104 });
        this.load.spritesheet('distiller_object', distiller_object, { frameWidth: 45, frameHeight: 58 });
        this.load.spritesheet('camera_object', camera_object, { frameWidth: 25, frameHeight: 17 });
        this.load.spritesheet('tube_object', tube_object, { frameWidth: 42, frameHeight: 61 });
        this.load.spritesheet('surgery_console_object', surgery_object, { frameWidth: 41, frameHeight: 52 });
        this.load.spritesheet('beds_object', beds_object, { frameWidth: 66, frameHeight: 58 });
        this.load.spritesheet('door_ground_tileset', door_ground_tileset, { frameWidth: 64, frameHeight: 36 });
        this.load.spritesheet('chair_object', chair_object, { frameWidth: 34, frameHeight: 36 });
        this.load.spritesheet('door_object', door_object, { frameWidth: 48, frameHeight: 73 });
        this.load.spritesheet('neron_terminal_object', neron_object, { frameWidth: 41, frameHeight: 64 });
        this.load.spritesheet('shelf_object', shelf_object, { frameWidth: 33, frameHeight: 40 });
        this.load.spritesheet('papers', papers, { frameWidth: 16, frameHeight: 12 });
        this.load.spritesheet('broom', broom, { frameWidth: 17, frameHeight: 29 });
        this.load.spritesheet('shelf_front_storage1', shelf_front_storage1, { frameWidth: 123, frameHeight: 101 });
        this.load.spritesheet('shelf_front_storage2', shelf_front_storage2, { frameWidth: 111, frameHeight: 91 });
        this.load.spritesheet('shelf_front_storage3', shelf_front_storage3, { frameWidth: 109, frameHeight: 74 });
        this.load.spritesheet('shelf_front_storage4', shelf_front_storage4, { frameWidth: 109, frameHeight: 79 });
        this.load.spritesheet('transparent_wall_object', transparent_wall_object, { frameWidth: 54, frameHeight: 69 });
        this.load.spritesheet('puddle', puddle, { frameWidth: 12, frameHeight: 8 });
        this.load.spritesheet('shower', shower, { frameWidth: 32, frameHeight: 60 });
        this.load.spritesheet('washRoom1', washRoom1, { frameWidth: 88, frameHeight: 90 });
        this.load.spritesheet('washRoom2', washRoom2, { frameWidth: 95, frameHeight: 92 });
        this.load.spritesheet('towelRack', towelRack, { frameWidth: 16, frameHeight: 26 });
        this.load.spritesheet('slippers', slippers, { frameWidth: 13, frameHeight: 9 });
        this.load.spritesheet('poster', poster, { frameWidth: 18, frameHeight: 31 });
        this.load.spritesheet('garden_equipment', garden_equipment, { frameWidth: 219, frameHeight: 156 });
        this.load.spritesheet('garden_console', garden_console, { frameWidth: 45, frameHeight: 42 });
        this.load.spritesheet('garden_engine', garden_engine, { frameWidth: 140, frameHeight: 112 });
        this.load.spritesheet('pneumatic_distributor', pneumatic_distributor, { frameWidth: 35, frameHeight: 42 });
        this.load.spritesheet('pneumatic_distributor_2', pneumatic_distributor_2, { frameWidth: 31, frameHeight: 41 });
        this.load.spritesheet('kitchen_1', kitchen_1, { frameWidth: 159, frameHeight: 111 });
        this.load.spritesheet('kitchen_2', kitchen_2, { frameWidth: 46, frameHeight: 62 });
        this.load.spritesheet('table', table, { frameWidth: 125, frameHeight: 81 });
        this.load.spritesheet('coffee_machine', coffee_machine, { frameWidth: 31, frameHeight: 52 });
        this.load.spritesheet('oxygen_tank', oxygen_tank, { frameWidth: 45, frameHeight: 45 });
        this.load.spritesheet('shelf_center_alpha_storage_1', shelf_center_alpha_storage_1, { frameWidth: 105, frameHeight: 80 });
        this.load.spritesheet('shelf_center_alpha_storage_2', shelf_center_alpha_storage_2, { frameWidth: 77, frameHeight: 72 });
        this.load.spritesheet('shelf_center_bravo_storage_1', shelf_center_bravo_storage_1, { frameWidth: 106, frameHeight: 93 });
        this.load.spritesheet('shelf_center_bravo_storage_2', shelf_center_bravo_storage_2, { frameWidth: 65, frameHeight: 50 });
        this.load.spritesheet('shelf_center_bravo_storage_3', shelf_center_bravo_storage_3, { frameWidth: 64, frameHeight: 64 });
        this.load.spritesheet('nexus_lamp', nexus_lamp, { frameWidth: 107, frameHeight: 59 });
        this.load.spritesheet('bios_terminal_calculator', bios_terminal_calculator, { frameWidth: 32, frameHeight: 60 });
        this.load.spritesheet('neron_core', neron_core, { frameWidth: 87, frameHeight: 90 });
        this.load.spritesheet('fuel_tank', fuel_tank, { frameWidth: 46, frameHeight: 45 });
        this.load.spritesheet('shelf_rear_alpha_storage', shelf_rear_alpha_storage, { frameWidth: 50, frameHeight: 48 });
        this.load.spritesheet('shelf_rear_bravo_storage', shelf_rear_bravo_storage, { frameWidth: 192, frameHeight: 134 });
        this.load.spritesheet('workshop', workshop, { frameWidth: 87, frameHeight: 81 });
        this.load.spritesheet('worktable', worktable, { frameWidth: 45, frameHeight: 54 });
        this.load.spritesheet('board', board, { frameWidth: 34, frameHeight: 56 });
        this.load.spritesheet('garden_engine_anim', garden_engine_anim, { frameWidth: 56, frameHeight: 27 });
        this.load.spritesheet('patrol_ship', patrol_ship, { frameWidth: 103, frameHeight: 78 });
        this.load.spritesheet('small_takeoff_platform', small_takeoff_platform, { frameWidth: 194, frameHeight: 106 });
        this.load.spritesheet('bay_door', bay_door, { frameWidth: 334, frameHeight: 230 });
        this.load.spritesheet('yellow_lamp', yellow_lamp, { frameWidth: 54, frameHeight: 69 });
        this.load.spritesheet('jukebox', jukebox, { frameWidth: 20, frameHeight: 31 });
        this.load.spritesheet('floor_lamp', floor_lamp, { frameWidth: 32, frameHeight: 24 });
        this.load.spritesheet('magnetic_net', magnetic_net, { frameWidth: 48, frameHeight: 33 });
        this.load.spritesheet('pasiphae', pasiphae, { frameWidth: 106, frameHeight: 93 });
        this.load.spritesheet('dynarcade', dynarcade, { frameWidth: 77, frameHeight: 82 });
        this.load.spritesheet('bay', bay, { frameWidth: 27, frameHeight: 27 });
        this.load.spritesheet('icarus_wall', icarus_wall, { frameWidth: 83, frameHeight: 55 });
        this.load.spritesheet('icarus_access', icarus_access, { frameWidth: 171, frameHeight: 140 });
        this.load.spritesheet('takeoff_platform', takeoff_platform, { frameWidth: 328, frameHeight: 206 });
        this.load.spritesheet('magnetic_return', magnetic_return, { frameWidth: 51, frameHeight: 35 });
        this.load.spritesheet('turret_tags', turret_tags, { frameWidth: 18, frameHeight: 10 });
        this.load.spritesheet('turret_back_bravo', turret_back_bravo, { frameWidth: 69, frameHeight: 26 });
        this.load.spritesheet('turret_back_alpha', turret_back_alpha, { frameWidth: 71, frameHeight: 46 });
        this.load.spritesheet('turret_ground', turret_ground, { frameWidth: 32, frameHeight: 11 });
        this.load.spritesheet('alpha_turret_front', alpha_turret_front, { frameWidth: 123, frameHeight: 61 });
        this.load.spritesheet('bravo_turret_front', bravo_turret_front, { frameWidth: 122, frameHeight: 48 });
        this.load.spritesheet('structure', structure, { frameWidth: 175, frameHeight: 106 });

        this.load.spritesheet('aeration_grid', aeration_grid, { frameWidth: 54, frameHeight: 29 });
        this.load.spritesheet('astro_terminal', astro_terminal, { frameWidth: 77, frameHeight: 72 });
        this.load.spritesheet('astro_terminal2', astro_terminal2, { frameWidth: 52, frameHeight: 46 });
        this.load.spritesheet('cockpit_window', cockpit_window, { frameWidth: 205, frameHeight: 166 });
        this.load.spritesheet('command_board', command_board, { frameWidth: 120, frameHeight: 106 });
        this.load.spritesheet('command_terminal', command_terminal, { frameWidth: 94, frameHeight: 52 });
        this.load.spritesheet('comms_center', comms_center, { frameWidth: 64, frameHeight: 48 });
        this.load.spritesheet('comms_center2', comms_center2, { frameWidth: 86, frameHeight: 61 });
        this.load.spritesheet('floor_lamp_bridge', floor_lamp_bridge, { frameWidth: 96, frameHeight: 79 });
        this.load.spritesheet('scanner', scanner, { frameWidth: 60, frameHeight: 71 });
        this.load.spritesheet('semi_circle_lamp', semi_circle_lamp, { frameWidth: 104, frameHeight: 51 });
        this.load.spritesheet('semicircle_floor', semicircle_floor, { frameWidth: 72, frameHeight: 37 });
        this.load.spritesheet('antenna', antenna, { frameWidth: 51, frameHeight: 68 });
        this.load.spritesheet('combustion_chamber', combustion_chamber, { frameWidth: 73, frameHeight: 63 });
        this.load.spritesheet('engine_room1', engine_room1, { frameWidth: 240, frameHeight: 186 });
        this.load.spritesheet('engine_room2', engine_room2, { frameWidth: 350, frameHeight: 188 });
        this.load.spritesheet('paraboles', paraboles, { frameWidth: 135, frameHeight: 133 });
        this.load.spritesheet('pilgred', pilgred, { frameWidth: 128, frameHeight: 116 });
        this.load.spritesheet('planet_scanner', planet_scanner, { frameWidth: 67, frameHeight: 60 });
        this.load.spritesheet('quantum_sensors', quantum_sensors, { frameWidth: 31, frameHeight: 43 });
        this.load.spritesheet('reactor', reactor, { frameWidth: 199, frameHeight: 181 });
        this.load.spritesheet('terminal_pilgred', terminal_pilgred, { frameWidth: 83, frameHeight: 69 });
        this.load.spritesheet('sofa_asset', sofa_asset, { frameWidth: 62, frameHeight: 47 });
        this.load.spritesheet('small_sofa', small_sofa, { frameWidth: 42, frameHeight: 37 });


        this.load.spritesheet('ground_object', ground_tileset, { frameWidth: 32, frameHeight: 72 });



        this.load.image('smoke_particle', smoke_particle);
        this.load.atlas('fire_particles', fire_particles, fire_particles_frame);
        this.load.image('tile_highlight', tile_highlight);
        this.load.image('hunter', hunter);
    }

    create(): void
    {
        (<Phaser.Renderer.WebGL.WebGLRenderer>this.game.renderer).pipelines.addPostPipeline('outline', OutlinePostFx);

        this.map = this.createRoom();

        this.createEquipments(this.map);
        this.updateStatuses();
        this.updateEquipments();

        //const loadPlayer = mapActions('player', ['loadPlayer']);
        store.subscribeAction({
            after: (action) => {
                if (action.type === 'player/reloadPlayer') {
                    this.reloadScene();
                }
            }
        });


        this.createBackground();
        this.enableEventListeners();

        if (this.player?.room?.type !== 'room') {
            return;
        }

        this.input.setTopOnly(true);
        this.input.setGlobalTopOnly(true);

        this.createPlayers();
    }

    reloadScene(): void
    {
        this.player = store.getters["player/player"];

        const newRoom = this.player.room;
        if (newRoom === null) { throw new Error("player room should be defined");}

        if (this.room.key !== newRoom.key) {
            this.room = newRoom;

            this.selectedGameObject = null;
            store.dispatch('room/selectTarget', { target: null });
            store.dispatch('room/closeInventory');

            this.deleteWallAndFloor();
            this.deleteCharacters();
            this.deleteEquipmentsAndDecoration();
            this.removeFire();

            this.map = this.createRoom();
            this.createEquipments(this.map);
            this.updateEquipments();
            if (this.room.type !== 'room') {
                return;
            }
            this.updateStatuses();
            this.createPlayers();

        } else if (this.areEquipmentsModified()) {
            this.navMeshGrid = new NavMeshGrid(this);
            this.room = newRoom;

            this.deleteEquipmentsAndDecoration();
            this.selectedGameObject = null;
            store.dispatch('room/selectTarget', { target: null });
            store.dispatch('room/closeInventory');

            if (this.map === null) { throw new Error("player room should be defined");}

            this.deleteCharacters();

            this.map = this.createRoom();
            this.createEquipments(this.map);
            this.updateStatuses();
            this.createPlayers();
        } else{
            this.room = newRoom;

            this.updatePlayers();
            this.updateEquipments();
            this.updateStatuses();
        }

        // update background
        this.updateBackground(newRoom);
    }

    updateStatuses(): void
    {
        if (this.room.isOnFire && this.fireParticles.length === 0) {
            this.displayFire();
        } else if (!this.room.isOnFire && this.fireParticles.length > 0) {
            this.removeFire();
        }
    }

    updateEquipments(): void
    {
        const sceneGameObjects = this.children.list;

        const room = this.player.room;

        if (room === null) { throw new Error("player room should be defined");}
        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if (gameObject instanceof EquipmentObject) {
                const updatedEquipment = room.equipments.filter((equipment: Equipment) => (equipment.id === gameObject.equipment.id))[0];

                gameObject.updateEquipment(updatedEquipment);

            } else if (gameObject instanceof DoorObject || gameObject instanceof DoorGroundObject) {
                const updatedDoor = room.doors.filter((door: Door) => (door.key === gameObject.door.key))[0];

                gameObject.updateDoor(updatedDoor);
            }
        }
    }

    updatePlayers(): void
    {
        const sceneGameObjects = this.children.list;
        const addedPlayer: Array<string> = [];

        const room = this.player.room;
        if (room === null) { throw new Error("player room should be defined");}

        // update player (that get up for instance) and remove player that moved or died
        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if (gameObject instanceof CharacterObject) {
                addedPlayer.push(gameObject.name);

                if (room.players.filter((player: Player) => {return player.character.key === gameObject.name;}).length == 0 &&
                    this.player.character.key !== gameObject.name
                ) {
                    gameObject.delete();
                    i = i-1;
                } else {
                    if (this.player.character.key === gameObject.name) {
                        const playerEntity = this.player;

                        gameObject.updatePlayer(playerEntity);
                        if (gameObject.name === this.selectedGameObject?.name) {
                            store.dispatch('room/selectTarget', { target: gameObject.player });
                        }
                    } else {
                        const playerEntity = room.players.filter((player: Player) => {return player.character.key === gameObject.name;})[0];

                        gameObject.updatePlayer(playerEntity);
                        if (gameObject.name === this.selectedGameObject?.name) {
                            store.dispatch('room/selectTarget', { target: gameObject.player });
                        }
                    }
                }
            }
        }

        //add players
        for (let i=0; i < room.players.length; i++) {
            const player = room.players[i];

            if (!addedPlayer.includes(player.character.key)) {
                const otherPlayerCoordinates = this.navMeshGrid.getRandomPoint();
                new CharacterObject(
                    this,
                    otherPlayerCoordinates,
                    new IsometricGeom(otherPlayerCoordinates.toIsometricCoordinates(), this.playerIsoSize),
                    player
                );
            }
        }
    }

    areEquipmentsModified(): boolean
    {
        const sceneGameObjects = this.children.list;

        const room = this.player.room;

        if (room === null) { throw new Error("player room should be defined");}
        const equipmentsToUpdate = room.equipments;

        const updatedEquipment = new Array<number>();

        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if (gameObject instanceof EquipmentObject) {
                if (equipmentsToUpdate.filter((equipment: Equipment) => {
                    return equipment.id === gameObject.equipment.id;
                }).length === 0) {
                    return true;
                }
                if (!(updatedEquipment.includes(gameObject.equipment.id))) {
                    updatedEquipment.push(gameObject.equipment.id);
                }
            }
        }

        return equipmentsToUpdate.length !== updatedEquipment.length;
    }

    createRoom(): MushTiledMap
    {
        this.sceneGrid = new SceneGrid(this, this.characterSize);
        this.navMeshGrid = new NavMeshGrid(this);

        const map = new MushTiledMap(this, this.room.key);
        this.roomBasicSceneGrid = map.createInitialSceneGrid(this.sceneGrid);
        this.sceneIsoSize = map.getMapSize();
        this.cameraDirection = new CartesianCoordinates(0,0);
        map.createLayers(this.room, this.sceneGrid);

        this.playerSprite = new PlayableCharacterObject(
            this,
            new CartesianCoordinates(0,0),
            new IsometricGeom(new IsometricCoordinates(0,0), this.playerIsoSize),
            this.player
        );

        if (this.room.type === 'patrol_ship') {
            this.playerSprite.setVisible(false);
        } else if (this.room.type === 'space') {
            this.playerSprite.play('space_giggle');
            this.playerSprite.setDepth(15);
        }

        //place the starting camera.
        //If the scene size is larger than the camera, the camera is centered on the player
        //else it is centered on the scene
        const sceneCartesianSize = new CartesianCoordinates(this.sceneIsoSize.x + this.sceneIsoSize.y, (this.sceneIsoSize.x + this.sceneIsoSize.y)/2);
        //this.cameras.main.setBounds(-this.sceneIsoSize.y, 0, sceneCartesianSize.x, sceneCartesianSize.y);


        this.cameras.main.setBounds(-this.game.scale.gameSize.width/2, -this.game.scale.gameSize.height/2 +72, sceneCartesianSize.x, sceneCartesianSize.y);

        if (sceneCartesianSize.x - 80 > this.game.scale.gameSize.width) {
            this.isScreenSliding.x = true;
            this.cameras.main.setBounds(-this.sceneIsoSize.y, -72, sceneCartesianSize.x, sceneCartesianSize.y + 72);
        }
        if (sceneCartesianSize.y - 80 > this.game.scale.gameSize.height) {
            this.isScreenSliding.y = true;
            this.cameras.main.setBounds(-this.sceneIsoSize.y, -72, sceneCartesianSize.x, sceneCartesianSize.y + 72);
        }

        // add target tile highlight
        this.targetHighlightObject = new Phaser.GameObjects.Sprite(this, 0, 0, 'tile_highlight');
        this.add.existing(this.targetHighlightObject);
        this.targetHighlightObject.setDepth(500);

        return map;
    }

    createBackground(): void
    {
        this.background = this.add.tileSprite(this.game.scale.gameSize.width/2, this.game.scale.gameSize.height/2, 425, 470, 'background');
        this.background.setScrollFactor(0, 0);
        this.background.setDepth(0);

        const daedalus = this.player.daedalus;
        if (daedalus === null) {
            return;
        }

        // add a planet in background if necessary
        const planet = daedalus.inOrbitPlanet;
        if (planet !== null) {
            this.displayPlanet(planet);
        }

        // add stars in the background with a particle emitter
        this.isTravelling = daedalus.isDaedalusTravelling;
        this.createStarParticles();

        this.attackingHunters = daedalus.attackingHunters;
        this.createHunterParticles();
    }

    updateBackground(newRoom: Room): void
    {
        const daedalus = this.player.daedalus;

        if (daedalus === null) {
            return;
        }

        // check if daedalus is traveling
        if (this.isTravelling !== daedalus.isDaedalusTravelling) {
            this.isTravelling = !this.isTravelling;
            this.createStarParticles();
        }

        // check if player took-off or land
        if (newRoom.type !== this.room?.type) {
            this.createStarParticles();
        }

        // check if hunter are attacking
        if (this.attackingHunters !== daedalus.attackingHunters) {
            this.attackingHunters = daedalus.attackingHunters;
            this.createHunterParticles();
        }

        //check if there is a planet in orbit
        const planet = daedalus.inOrbitPlanet;
        if (planet !== null) {
            this.displayPlanet(planet);
        }
    }

    displayPlanet(inOrbitPlanet: Planet): void
    {
        const planetSprite = this.add.tileSprite(
            this.game.scale.gameSize.width-(268/2),
            this.game.scale.gameSize.height-(191/2),
            268, 191,
            `planet_${inOrbitPlanet.imageId}`
        );
        planetSprite.setScrollFactor(0, 0);
        planetSprite.setDepth(3);
    }

    createHunterParticles(): void
    {
        let displayedHunter = 0;
        let hunterFrequency = 10000;

        this.hunterParticle?.destroy();

        if (this.attackingHunters === 0) {
            return;
        } else if (this.attackingHunters <= 1) {
            displayedHunter = 1;
        } else if (this.attackingHunters <= 5) {
            displayedHunter = 2;
        } else if (this.attackingHunters <= 10) {
            displayedHunter = 3;
        } else {
            displayedHunter = 3;
            hunterFrequency = 5000;
        }

        const gameSize = this.game.scale.gameSize;
        const hunterAngle = 145;
        const maxSpawnY = gameSize.height * 2/3 - Math.tan(180 - hunterAngle);
        const minSpawnY = - Math.tan(180 - hunterAngle) * gameSize.width/2;

        const gameLimits = new Phaser.Geom.Rectangle(
            -10, minSpawnY - 5,
            gameSize.width + 130, gameSize.height - minSpawnY + 10,
        );


        const grpY: any[] = [];
        const getNextY = () => {
            if(!grpY.length){
                const center = minSpawnY + Math.random() * (maxSpawnY- minSpawnY);
                grpY.push(center - 30, center, center + 30);
            }
            return grpY.pop();
        };

        const grpX: any[] = [];
        const getNextX = () => {
            if(!grpX.length){
                const formation = Math.random();
                if (formation < 0.4) {
                    grpX.push(gameSize.width + 10, gameSize.width + 40, gameSize.width + 70);
                } else if (formation < 0.8) {
                    grpX.push(gameSize.width + 60, gameSize.width + 110, gameSize.width + 10);
                } else {
                    grpX.push(gameSize.width + 10, gameSize.width + 10, gameSize.width + 10);
                }
            }
            return grpX.pop();
        };

        const hunterEmitter = this.add.particles(0,0, 'hunter', {
            x: getNextX,
            y: getNextY,
            lifespan: 2000,
            speed: 700,
            angle: hunterAngle,
            quantity: { min: 1, max: displayedHunter },
            frequency: hunterFrequency,
            accelerationY: 2,
            accelerationX: 2,
        });
        hunterEmitter.setDepth(3);
        hunterEmitter.addDeathZone(new DeathZone(gameLimits, false));
        hunterEmitter.setScrollFactor(0,0);

        this.hunterParticle = hunterEmitter;
    }

    createStarParticles(): void
    {
        this.removeStarEmitter();

        const gameSize = this.game.scale.gameSize;

        let starSpeed = 10;
        let starFrequency = 2000;
        let starAngle = 30;
        let horizontalEmitArea = new Phaser.Geom.Line(0,0,gameSize.width, 0);
        const verticalEmitArea = new Phaser.Geom.Line(0,0,0, gameSize.height);

        if (this.player.room?.type === 'patrol_ship') {
            starAngle = -30;
            starSpeed = 300;
            starFrequency = 1000;
            horizontalEmitArea = new Phaser.Geom.Line(0,gameSize.height,gameSize.width, gameSize.height);
        }


        if (this.isTravelling) {
            starSpeed = 1000;
            starFrequency = 50;
        }
        this.textures.generate('star_particles', { data: ['2'] });

        const gameLimits = new Phaser.Geom.Rectangle(
            -10, -10,
            gameSize.width + 20, gameSize.height + 20,
        );

        const topStarEmitter = this.add.particles(0,0, 'star_particles', {
            lifespan: 200000,
            speed: starSpeed,
            angle: starAngle,
            scale: { min: 1, max: 3 },
            quantity: 1,
            frequency: starFrequency,
            //@ts-ignore
            emitZone: { type: 'random', source: verticalEmitArea },
        });
        topStarEmitter.setDepth(1);
        topStarEmitter.setScrollFactor(0, 0);
        topStarEmitter.addDeathZone(new DeathZone(gameLimits, false));

        const leftStarEmitter = this.add.particles(0,0, 'star_particles', {
            lifespan: 200000,
            speed: starSpeed,
            angle: starAngle,
            scale: { min: 1, max: 3 },
            quantity: 1,
            frequency: starFrequency,
            //@ts-ignore
            emitZone: { type: 'random', source: horizontalEmitArea },
        });
        leftStarEmitter.setDepth(1);
        leftStarEmitter.setScrollFactor(0,0);
        leftStarEmitter.addDeathZone(new DeathZone(gameLimits, false));

        this.starParticles = [topStarEmitter, leftStarEmitter];
    }

    removeStarEmitter(): void
    {
        for (let i=0; i< this.starParticles.length; i++) {
            const particleEmitter = this.starParticles[i];
            particleEmitter.destroy();
        }
        this.starParticles = [];
    }

    createEquipments(map: MushTiledMap): void
    {
        this.equipments = map.createEquipmentLayers(this.room, this.roomBasicSceneGrid);

        this.sceneGrid.updateDepth();
        this.navMeshGrid = this.sceneGrid.buildNavMeshGrid();
    }

    deleteEquipmentsAndDecoration(): void
    {
        const sceneGameObjects = this.children.list;
        const room = this.player.room;
        if (room === null) { throw new Error("player room should be defined");}

        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if (gameObject instanceof DecorationObject &&
                !(gameObject instanceof CharacterObject))
            {
                gameObject.delete();
                i = i-1;
            }
        }
    }

    deleteWallAndFloor(): void
    {
        const sceneGameObjects = this.children.list;
        const room = this.player.room;
        if (room === null) { throw new Error("player room should be defined");}

        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            // do not remove backgroud and star particles
            if (!(gameObject instanceof DecorationObject) &&
                gameObject !== this.background &&
                !(gameObject instanceof  Phaser.GameObjects.Particles.ParticleEmitter)
            ){
                gameObject.destroy();
                i = i-1;
            }
        }
    }

    deleteCharacters(): void
    {
        const sceneGameObjects = this.children.list;
        const room = this.player.room;
        if (room === null) { throw new Error("player room should be defined");}

        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if ((gameObject instanceof CharacterObject))
            {
                gameObject.delete();
                i = i-1;
            }
        }
    }

    removeFire(): void
    {
        for (let i=0; i< this.fireParticles.length; i++) {
            const particleEmitter = this.fireParticles[i];
            particleEmitter.destroy();
            this.fireParticles.splice(i, 1);
            i= i-1;
        }
    }

    displayFire(): void
    {
        const totalNumberOfTiles = this.sceneIsoSize.x * this.sceneIsoSize.y/(this.isoTileSize * this.isoTileSize);

        const numberOfFireCells = (Math.random()*0.2) * totalNumberOfTiles + 3;

        for (let i = 0; i < numberOfFireCells; i++) {
            //get random coordinates for the fire cell
            const rand_iso_coords = this.navMeshGrid.getRandomPoint().toIsometricCoordinates();
            const cell_coords = this.getGridIsoCoordinate(rand_iso_coords);

            if (this.sceneGrid.getPolygonFromPoint(cell_coords) !== -1) {
                //intensity of fire
                if (Math.random() > 0.2) {
                    this.createFireCell(cell_coords, 1);
                } else {
                    this.createFireCell(cell_coords, 2);
                }
            }
        }
    }

    createFireCell(isoCoords: IsometricCoordinates, intensity: number): void
    {
        const tile = new IsometricGeom(isoCoords, new IsometricCoordinates(16, 16));

        const yellowFlames = this.add.particles(0,0, 'fire_particles', {
            frame: ['flame1', 'flame2'],
            x: 0, y: 0,
            lifespan: 200,
            speed: { min: 30, max: 50 },
            angle: { min: 260, max: 280 },
            gravityY: 50,
            scale: { start: 1, end: 1 },
            alpha: { start: 0, end: 0.8 },
            quantity: 5,
            //@ts-ignore
            emitZone: { type: 'random', source: tile }
        });
        yellowFlames.setDepth(this.sceneGrid.getDepthOfPoint(isoCoords));
        this.fireParticles.push(yellowFlames);


        if (intensity > 1) {
            const redFlames = this.add.particles(0,0, 'fire_particles', {
                frame: ['flame2','flame3'],
                x: 0, y: 0,
                lifespan: 600,
                speed: { min: 40, max: 60 },
                angle: { min: 260, max: 280 },
                gravityY: 40,
                scale: { start: 0, end: 1 },
                alpha: { start: 0, end: 0.8 },
                quantity: 2,
                //@ts-ignore
                emitZone: { type: 'random', source: tile }
            });
            redFlames.setDepth(this.sceneGrid.getDepthOfPoint(isoCoords));
            this.fireParticles.push(redFlames);
        }

        const smoke = this.add.particles(0,0, 'fire_particles', {
            frame: ['flame4','flame5','flame6'],
            x: 0, y: -8,
            lifespan: 800,
            speed: { min: 20, max: 40 },
            angle: { min: 260, max: 280 },
            gravityY: 20,
            scale: { start: 0, end: 1 },
            alpha: { start: 0, end: 0.5 },
            quantity: 2,
            //@ts-ignore
            emitZone: { type: 'random', source: tile }
        });
        smoke.setDepth(this.sceneGrid.getDepthOfPoint(isoCoords));
        this.fireParticles.push(smoke);
    }

    handleSpaceBattle(time: number, delta: number): void
    {
        if (this.room?.type === 'patrol_ship') {
            const sceneGameObjects = this.children.list;

            for (let i=0; i < sceneGameObjects.length; i++) {
                const gameObject = sceneGameObjects[i];

                if (gameObject instanceof EquipmentObject && (
                    gameObject.equipment.key?.substring(0, 11) === 'patrol_ship' ||
                    gameObject.equipment.key?.substring(0, 8) === 'pasiphae')
                ) {
                    gameObject.update(time, delta);
                }
            }
        }
    }
    update(time: number, delta: number): void
    {
        this.playerSprite.update();

        this.handleSpaceBattle(time, delta);

        if (this.targetHighlightObject !== undefined) {
            const worldPointer = this.input.mousePointer.updateWorldPoint(this.cameras.main);
            const pointerCoords = new CartesianCoordinates(worldPointer.worldX, worldPointer.worldY);
            const cellCoords = this.getGridIsoCoordinate(pointerCoords.toIsometricCoordinates()).toCartesianCoordinates();

            const sceneGridIndex = this.sceneGrid.getPolygonFromPoint(cellCoords.toIsometricCoordinates());

            if (sceneGridIndex !== -1) {
                this.targetHighlightObject.setPosition(cellCoords.x, cellCoords.y);
                this.targetHighlightObject.setDepth(this.sceneGrid.getDepthOfPoint(cellCoords.toIsometricCoordinates()));
            } else {
                this.targetHighlightObject.setDepth(0);
            }
        }

        // camera
        //this.cameras.main.centerOn(this.cameraTarget.x, this.cameraTarget.y);
        if (this.cameraDirection.x !== 0 || this.cameraDirection.y !== 0) {
            this.cameras.main.scrollX += this.cameraDirection.x;
            this.cameras.main.scrollY += this.cameraDirection.y;

            if (((this.cameraDirection.x >= 0 && this.cameras.main.scrollX >= this.cameraTarget.x) ||
                (this.cameraDirection.x <= 0 && this.cameras.main.scrollX <= this.cameraTarget.x)) &&
                ((this.cameraDirection.y >= 0 && this.cameras.main.scrollY >= this.cameraTarget.y) ||
                (this.cameraDirection.y <= 0 && this.cameras.main.scrollY <= this.cameraTarget.y))
            ) {
                this.cameraDirection.setTo(0,0);
            }
        }
    }

    // return the center of the currently pointed tile
    getGridIsoCoordinate(isoCoord: IsometricCoordinates): IsometricCoordinates
    {
        return new IsometricCoordinates(
            Math.floor(((isoCoord.x + 4)/this.isoTileSize)) * this.isoTileSize,
            Math.floor(((isoCoord.y + 4)/this.isoTileSize)) * this.isoTileSize
        );
    }

    createPlayers(): void
    {
        let playerCoordinates = this.navMeshGrid.getRandomPoint();
        if (this.previousRoom !== undefined && this.previousRoom !== this.room.key) {
            playerCoordinates = this.findRoomEntryPoint();
            this.playerSprite.interactedEquipment = null;
        }

        this.previousRoom = this.room.key;
        this.cameras.main.centerOn(playerCoordinates.x, playerCoordinates.y);

        this.playerSprite.setPositionFromFeet(playerCoordinates);
        this.playerSprite.updateNavMesh();
        this.playerSprite.checkPositionDepth();
        this.playerSprite.applyEquipmentInteraction();
        this.playerSprite.resetMove();

        this.room.players.forEach((roomPlayer: Player) => {
            if (roomPlayer.id !== this.player.id) {
                const otherPlayerCoordinates = this.navMeshGrid.getRandomPoint();
                new CharacterObject(
                    this,
                    otherPlayerCoordinates,
                    new IsometricGeom(otherPlayerCoordinates.toIsometricCoordinates(), this.playerIsoSize),
                    roomPlayer
                );
            }
        });
    }

    findRoomEntryPoint(): CartesianCoordinates
    {
        const sceneGameObjects = this.children.list;

        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if (gameObject instanceof DoorGroundObject &&
                gameObject.door.direction === this.previousRoom)
            {
                return this.navMeshGrid.getClosestPoint(gameObject.isoGeom.getIsoCoords()).toCartesianCoordinates();
            }
        }
        return this.navMeshGrid.getRandomPoint();
    }

    enableEventListeners(): void
    {
        this.input.on('pointerdown', (pointer: Phaser.Input.Pointer, gameObjects: Array<Phaser.GameObjects.GameObject>) => {
            let gameObject = null;
            if (gameObjects.length>0) {
                gameObject = gameObjects[0];
            }

            this.playerSprite.updateMovement(pointer, gameObject);

            if (this.selectedGameObject !== null &&
                this.selectedGameObject instanceof InteractObject &&
                this.selectedGameObject !== gameObject
            ) {
                this.selectedGameObject.onClickedOut();

                this.selectedGameObject = gameObject;

                if (gameObject === null) {
                    store.dispatch('room/selectTarget', { target: null });
                    store.dispatch('room/closeInventory');
                }
            }
            if (gameObject instanceof InteractObject){
                gameObject.onSelected();
                this.selectedGameObject = gameObject;
            }
            if (gameObject instanceof DoorObject) {
                gameObject.onDoorClicked(pointer);
            }


            // screen sliding
            const playerTargetCoordinates = this.playerSprite.getMovementTarget();

            if (playerTargetCoordinates !== null) {
                const requiredScroll = this.cameras.main.getScroll(playerTargetCoordinates.x, playerTargetCoordinates.y);

                if (!this.isScreenSliding.x) {requiredScroll.x = this.cameras.main.scrollX;}
                if (!this.isScreenSliding.y) {requiredScroll.y = this.cameras.main.scrollY;}
                if (requiredScroll.x !== this.cameras.main.scrollX || requiredScroll.y !== this.cameras.main.scrollY) {
                    this.cameraTarget.setTo(requiredScroll.x, requiredScroll.y);

                    const norm = Math.pow(
                        Math.pow((requiredScroll.x - this.cameras.main.scrollX), 2)+
                        Math.pow((requiredScroll.y - this.cameras.main.scrollY), 2),
                        1/2
                    );
                    this.cameraDirection.setTo(
                        (requiredScroll.x  - this.cameras.main.scrollX)/norm,
                        (requiredScroll.y  - this.cameras.main.scrollY)/norm
                    );
                }
            }
        });

        this.input.on('gameobjectout', () => {
            if (this.targetHighlightObject !== undefined) {
                this.targetHighlightObject.setAlpha(1);
            }
        });
        this.input.on('gameobjectover', () => {
            if (this.targetHighlightObject !== undefined) {
                this.targetHighlightObject.setAlpha(0);
            }
        });
    }

    findObjectByNameAndId(name: string, id: number) : EquipmentObject | null
    {
        for (let i = 0; i< this.equipments.length; i++) {
            const equipment = this.equipments[i];
            if (equipment.equipment.key === name && equipment.equipment.id === id) {
                return equipment;
            }
        }

        return null;
    }

    enableDebugView(): void
    {
        // navMesh Debug
        //const navMeshPolygons = this.navMeshGrid.geomArray;
        const navMeshPolygons = this.sceneGrid.depthSortingArray;

        const debugGraphics = this.add.graphics().setAlpha(1);
        debugGraphics.setDepth(1000000000);
        for (let i = 0; i < navMeshPolygons.length; i++) {
        // for (let i = 4; i < 5; i++) {
            const polygon = navMeshPolygons[i].geom.getIsoArray();
            //const polygon = navMeshPolygons[i].getIsoArray();

            //console.log(navMeshPolygons[i].object?.name);
            //console.log(navMeshPolygons[i].object?.depth);

            let maxX = polygon[0].x;
            let minX = polygon[0].x;
            let maxY = polygon[0].y;
            let minY = polygon[0].y;

            polygon.forEach((point: {x: number, y: number}) => {
                if (point.x >maxX) { maxX = point.x; }
                if (point.y >maxY) { maxY = point.y; }
                if (point.x <minX) { minX = point.x; }
                if (point.y <minY) { minY = point.y; }
            });

            const cartPoly = new IsometricGeom(new IsometricCoordinates((maxX+minX)/2, (maxY+minY)/2), new IsometricCoordinates(maxX-minX, maxY-minY));

            if (navMeshPolygons[i].isNavigable) {
                debugGraphics.fillStyle(0x00FF00, 0.1);
            } else {
                debugGraphics.fillStyle(0xFF0000, 0.1);
            }
            //debugGraphics.fillStyle(0xF0FFFF, 0.2);

            debugGraphics.lineStyle(1, 0xff0000, 1.0);
            debugGraphics.fillPoints(cartPoly.getCartesianPolygon().points, true);
            debugGraphics.strokePoints(cartPoly.getCartesianPolygon().points, true);
        }

        // const debugGraphics2 = this.add.graphics().setAlpha(1);
        // debugGraphics2.setDepth(100000000);
        // this.navMeshGrid.phaserNavMesh.enableDebug(debugGraphics2);
        // this.navMeshGrid.phaserNavMesh.debugDrawClear(); // Clears the overlay
        // // Visualize the underlying navmesh
        // this.navMeshGrid.phaserNavMesh.debugDrawMesh({
        //     drawCentroid: false,
        //     drawBounds: false,
        //     drawNeighbors: true,
        //     drawPortals: false
        // });
    }
}
