import Phaser from 'phaser';
import { Room } from "@/entities/Room";

import background from '@/game/assets/tilemaps/background.png';
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
import magnetic_return from "@/game/assets/tilemaps/magnetic_return.png";

import character from "@/game/assets/images/characters.png";
import CharacterObject from "@/game/objects/characterObject";
import InteractObject from "@/game/objects/interactObject";

import laboratory from "@/game/assets/mush_lab.json";
import medlab from "@/game/assets/mush_medlab.json";
import central_corridor from "@/game/assets/central_corridor.json";
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

import fire_particles_frame from "@/game/assets/images/fire_particles.json";
import fire_particles from "@/game/assets/images/fire_particles.png";
import smoke_particle from "@/game/assets/images/smoke_particle.png";
import tile_highlight from "@/game/assets/images/tile_highlight.png";

import OutlinePostFx from 'phaser3-rex-plugins/plugins/outlinepipeline.js';

import { Player } from "@/entities/Player";
import PlayableCharacterObject from "@/game/objects/playableCharacterObject";
import { IsometricCoordinates, CartesianCoordinates } from "@/game/types";
import IsometricGeom from "@/game/scenes/isometricGeom";
import { SceneGrid } from "@/game/scenes/sceneGrid";
import { NavMeshGrid } from "@/game/scenes/navigationGrid";
import store from "@/store";
import MushTiledMap from "@/game/tiled/mushTiledMap";


export default class DaedalusScene extends Phaser.Scene
{
    private characterSize = 6;
    private isoTileSize: number;
    private sceneIsoSize: IsometricCoordinates;

    public playerSprite! : PlayableCharacterObject;

    private player : Player;
    private room : Room;
    private cameraTarget : { x : number, y : number};
    private targetHighlightObject?: Phaser.GameObjects.Sprite;

    public sceneGrid: SceneGrid;
    public navMeshGrid: NavMeshGrid;

    public selectedGameObject : Phaser.GameObjects.GameObject | null;

    constructor(player: Player) {
        super('game-scene');

        this.isoTileSize = 16;
        this.sceneIsoSize= new IsometricCoordinates(0, 0);

        if (player.room === null){
            throw new Error('player should have a room');
        }

        this.room = player.room;
        this.player = player;

        this.sceneGrid = new SceneGrid(this, this.characterSize);
        this.navMeshGrid = new NavMeshGrid(this);

        this.selectedGameObject = null;

        this.cameraTarget = { x: 0 , y: 0 };
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

        this.load.image('ground_tileset', ground_tileset);
        this.load.image('wall_tileset', wall_tileset);
        this.load.image('background', background);

        this.load.spritesheet('character', character, { frameHeight: 48, frameWidth: 32 });

        this.load.spritesheet('centrifuge_object', centrifuge_object, { frameHeight: 34, frameWidth: 30 });
        this.load.spritesheet('desk_object', desk_object, { frameHeight: 37, frameWidth: 45 });
        this.load.spritesheet('paper_dispenser', paper_dispenser, { frameHeight: 15, frameWidth: 9 });
        this.load.spritesheet('laboratory_object', laboratory_object, { frameHeight: 57, frameWidth: 79 });
        this.load.spritesheet('mural_shelf', mural_shelf_object, { frameHeight: 28, frameWidth: 46 });
        this.load.spritesheet('mycoscan_object', mycoscan_object, { frameHeight: 57, frameWidth: 81 });

        this.load.spritesheet('gravity_object', gravity_object, { frameHeight: 46, frameWidth: 28 });
        this.load.spritesheet('wall_box', wall_box, { frameHeight: 15, frameWidth: 14 });
        this.load.spritesheet('cryomodule_object', cryomodule_object, { frameHeight: 104, frameWidth: 128 });
        this.load.spritesheet('distiller_object', distiller_object, { frameHeight: 58, frameWidth: 45 });
        this.load.spritesheet('camera_object', camera_object, { frameHeight: 17, frameWidth: 25 });
        this.load.spritesheet('tube_object', tube_object, { frameHeight: 61, frameWidth: 42 });
        this.load.spritesheet('surgery_console_object', surgery_object, { frameHeight: 52, frameWidth: 41 });
        this.load.spritesheet('beds_object', beds_object, { frameHeight: 58, frameWidth: 66 });
        this.load.spritesheet('door_ground_tileset', door_ground_tileset, { frameHeight: 36, frameWidth: 64 });
        this.load.spritesheet('chair_object', chair_object, { frameHeight: 36, frameWidth: 34 });
        this.load.spritesheet('door_object', door_object, { frameHeight: 73, frameWidth: 48 });
        this.load.spritesheet('neron_terminal_object', neron_object, { frameHeight: 64, frameWidth: 41 });
        this.load.spritesheet('shelf_object', shelf_object, { frameHeight: 40, frameWidth: 33 });
        this.load.spritesheet('papers', papers, { frameHeight: 12, frameWidth: 16 });
        this.load.spritesheet('broom', broom, { frameHeight: 29, frameWidth: 17 });
        this.load.spritesheet('shelf_front_storage1', shelf_front_storage1, { frameHeight: 101, frameWidth: 123 });
        this.load.spritesheet('shelf_front_storage2', shelf_front_storage2, { frameHeight: 91, frameWidth: 111 });
        this.load.spritesheet('shelf_front_storage3', shelf_front_storage3, { frameHeight: 74, frameWidth: 109 });
        this.load.spritesheet('shelf_front_storage4', shelf_front_storage4, { frameHeight: 79, frameWidth: 109 });
        this.load.spritesheet('transparent_wall_object', transparent_wall_object, { frameHeight: 69, frameWidth: 54 });
        this.load.spritesheet('puddle', puddle, { frameHeight: 8, frameWidth: 12 });
        this.load.spritesheet('shower', shower, { frameHeight: 60, frameWidth: 32 });
        this.load.spritesheet('washRoom1', washRoom1, { frameHeight: 90, frameWidth: 88 });
        this.load.spritesheet('washRoom2', washRoom2, { frameHeight: 92, frameWidth: 95 });
        this.load.spritesheet('towelRack', towelRack, { frameHeight: 26, frameWidth: 16 });
        this.load.spritesheet('slippers', slippers, { frameHeight: 9, frameWidth: 13 });
        this.load.spritesheet('poster', poster, { frameHeight: 31, frameWidth: 18 });
        this.load.spritesheet('garden_equipment', garden_equipment, { frameHeight: 156, frameWidth: 219 });
        this.load.spritesheet('garden_console', garden_console, { frameHeight: 42, frameWidth: 45 });
        this.load.spritesheet('garden_engine', garden_engine, { frameHeight: 112, frameWidth: 140 });
        this.load.spritesheet('pneumatic_distributor', pneumatic_distributor, { frameHeight: 42, frameWidth: 35 });
        this.load.spritesheet('pneumatic_distributor_2', pneumatic_distributor_2, { frameHeight: 41, frameWidth: 31 });
        this.load.spritesheet('kitchen_1', kitchen_1, { frameHeight: 111, frameWidth: 159 });
        this.load.spritesheet('kitchen_2', kitchen_2, { frameHeight: 62, frameWidth: 46 });
        this.load.spritesheet('table', table, { frameHeight: 81, frameWidth: 125 });
        this.load.spritesheet('coffee_machine', coffee_machine, { frameHeight: 52, frameWidth: 31 });
        this.load.spritesheet('oxygen_tank', oxygen_tank, { frameHeight: 45, frameWidth: 45 });
        this.load.spritesheet('shelf_center_alpha_storage_1', shelf_center_alpha_storage_1, { frameHeight: 80, frameWidth: 105 });
        this.load.spritesheet('shelf_center_alpha_storage_2', shelf_center_alpha_storage_2, { frameHeight: 72, frameWidth: 77 });
        this.load.spritesheet('shelf_center_bravo_storage_1', shelf_center_bravo_storage_1, { frameHeight: 93, frameWidth: 106 });
        this.load.spritesheet('shelf_center_bravo_storage_2', shelf_center_bravo_storage_2, { frameHeight: 50, frameWidth: 65 });
        this.load.spritesheet('shelf_center_bravo_storage_3', shelf_center_bravo_storage_3, { frameHeight: 64, frameWidth: 64 });
        this.load.spritesheet('nexus_lamp', nexus_lamp, { frameHeight: 59, frameWidth: 107 });
        this.load.spritesheet('bios_terminal_calculator', bios_terminal_calculator, { frameHeight: 60, frameWidth: 32 });
        this.load.spritesheet('neron_core', neron_core, { frameHeight: 90, frameWidth: 87 });
        this.load.spritesheet('fuel_tank', fuel_tank, { frameHeight: 45, frameWidth: 46 });
        this.load.spritesheet('shelf_rear_alpha_storage', shelf_rear_alpha_storage, { frameHeight: 48, frameWidth: 50 });
        this.load.spritesheet('shelf_rear_bravo_storage', shelf_rear_bravo_storage, { frameHeight: 134, frameWidth: 192 });
        this.load.spritesheet('workshop', workshop, { frameHeight: 81, frameWidth: 87 });
        this.load.spritesheet('worktable', worktable, { frameHeight: 54, frameWidth: 45 });
        this.load.spritesheet('board', board, { frameHeight: 56, frameWidth: 34 });
        this.load.spritesheet('garden_engine_anim', garden_engine_anim, { frameHeight: 27, frameWidth: 56 });
        this.load.spritesheet('patrol_ship', patrol_ship, { frameHeight: 78, frameWidth: 103 });
        this.load.spritesheet('small_takeoff_platform', small_takeoff_platform, { frameHeight: 106, frameWidth: 194 });
        this.load.spritesheet('bay_door', bay_door, { frameHeight: 230, frameWidth: 334 });
        this.load.spritesheet('yellow_lamp', yellow_lamp, { frameHeight: 69, frameWidth: 54 });
        this.load.spritesheet('jukebox', jukebox, { frameHeight: 31, frameWidth: 20 });
        this.load.spritesheet('floor_lamp', floor_lamp, { frameHeight: 24, frameWidth: 32 });
        this.load.spritesheet('magnetic_net', magnetic_net, { frameHeight: 33, frameWidth: 48 });
        this.load.spritesheet('pasiphae', pasiphae, { frameHeight: 93, frameWidth: 106 });
        this.load.spritesheet('dynarcade', dynarcade, { frameHeight: 82, frameWidth: 77 });
        this.load.spritesheet('bay', bay, { frameHeight: 27, frameWidth: 27 });
        this.load.spritesheet('icarus_wall', icarus_wall, { frameHeight: 55, frameWidth: 83 });
        this.load.spritesheet('icarus_access', icarus_access, { frameHeight: 140, frameWidth: 171 });
        this.load.spritesheet('takeoff_platform', takeoff_platform, { frameHeight: 206, frameWidth: 328 });
        this.load.spritesheet('magnetic_return', magnetic_return, { frameHeight: 35, frameWidth: 51 });


        this.load.spritesheet('ground_object', ground_tileset, { frameHeight: 72, frameWidth: 32 });

        this.load.image('smoke_particle', smoke_particle);
        this.load.atlas('fire_particles', fire_particles, fire_particles_frame);
        this.load.image('tile_highlight', tile_highlight);
    }

    // eslint-disable-next-line no-unused-vars
    create(): void
    {
        (<Phaser.Renderer.WebGL.WebGLRenderer>this.game.renderer).pipelines.addPostPipeline('outline', OutlinePostFx);

        const map = new MushTiledMap(this, this.room.key);

        map.createInitialSceneGrid(this.sceneGrid);
        map.createLayers(this.room, this.sceneGrid);

        // add target tile highlight
        this.targetHighlightObject = new Phaser.GameObjects.Sprite(this, 0, 0, 'tile_highlight');
        this.add.existing(this.targetHighlightObject);
        this.targetHighlightObject.setDepth(500);

        this.sceneGrid.updateDepth();

        this.navMeshGrid = this.sceneGrid.buildNavMeshGrid();

        // this.enableDebugView();

        //place the starting camera.
        //If the scene size is larger than the camera, the camera is centered on the player
        //else it is centered on the scene
        const playerCoordinates = this.getPlayerCoordinates();

        const cameraPosition = map.getCameraPosition();
        this.cameras.main.setBounds(cameraPosition.x, cameraPosition.y, cameraPosition.width, cameraPosition.height);

        this.input.setTopOnly(true);
        this.input.setGlobalTopOnly(true);

        this.createPlayers(playerCoordinates);

        this.cameras.main.startFollow(this.playerSprite);

        this.enableEventListeners();

        this.add.tileSprite(0, 0, 848, 920, 'background');

        if (this.room.isOnFire) {
            this.displayFire();
        }
    }

    displayFire(): void
    {
        for (let i = this.isoTileSize/2; i < this.sceneIsoSize.x; i = i + this.isoTileSize) {
            for (let j = this.isoTileSize/2; j < this.sceneIsoSize.y; j = j + this.isoTileSize) {
                const tileCoordinates = new IsometricCoordinates(i, 8 + j);
                if (this.sceneGrid.getPolygonFromPoint(tileCoordinates) !== -1) {
                    //is the tile on fire
                    if (Math.random() < 0.2) {
                        //intensity of fire
                        if (Math.random() > 0.2) {
                            this.createFireCell(tileCoordinates, 1);
                        } else {
                            this.createFireCell(tileCoordinates, 2);
                        }
                    }
                }
            }
        }
    }

    createFireCell(isoCoords: IsometricCoordinates, intensity: number): void
    {
        const particles = this.add.particles('fire_particles');
        particles.setDepth(this.sceneGrid.getDepthOfPoint(isoCoords));

        const tile = new IsometricGeom(isoCoords, new IsometricCoordinates(16, 16));

        particles.createEmitter({
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

        if (intensity > 1) {
            particles.createEmitter({
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
        }

        particles.createEmitter({
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
    }

    update(time: number, delta: number): void
    {
        this.playerSprite.update();

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
    }

    // return the center of the currently pointed tile
    getGridIsoCoordinate(isoCoord: IsometricCoordinates): IsometricCoordinates
    {
        return new IsometricCoordinates(
            Math.floor(((isoCoord.x + 4)/this.isoTileSize)) * this.isoTileSize,
            Math.floor(((isoCoord.y + 4)/this.isoTileSize)) * this.isoTileSize
        );
    }

    createPlayers(playerCoordinates: CartesianCoordinates): void
    {
        const playerIsoSize = new IsometricCoordinates(this.characterSize, this.characterSize);
        let feetCenter = new CartesianCoordinates(playerCoordinates.x, playerCoordinates.y + 16);

        this.playerSprite = new PlayableCharacterObject(
            this,
            playerCoordinates,
            new IsometricGeom(feetCenter.toIsometricCoordinates(), playerIsoSize),
            this.player
        );

        this.room.players.forEach((roomPlayer: Player) => {
            if (roomPlayer.id !== this.player.id) {
                const otherPlayerCoordinates = this.getPlayerCoordinates();
                feetCenter = new CartesianCoordinates(otherPlayerCoordinates.x, otherPlayerCoordinates.y + 16);

                const newCharacter = new CharacterObject(
                    this,
                    otherPlayerCoordinates,
                    new IsometricGeom(feetCenter.toIsometricCoordinates(), playerIsoSize),
                    roomPlayer
                );
            }
        });
    }

    getPlayerCoordinates(): CartesianCoordinates
    {
        const cartCoords = this.navMeshGrid.getRandomPoint();
        //Coordinates of player in the navMesh is given relative to the feet of the player
        //Coordinates given in the constructor of player are the center of the sprite
        cartCoords.setTo(cartCoords.x, cartCoords.y - 16);

        return cartCoords;
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

    enableDebugView(): void
    {
        // navMesh Debug
        const navMeshPolygons = this.navMeshGrid.geomArray;
        //const navMeshPolygons = this.sceneGrid.depthSortingArray;

        const debugGraphics = this.add.graphics().setAlpha(1);
        debugGraphics.setDepth(1000000);
        for (let i = 0; i < navMeshPolygons.length; i++) {
        // for (let i = 4; i < 5; i++) {
            //const polygon = navMeshPolygons[i];
            const polygon = navMeshPolygons[i].getIsoArray();

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

            // if (navMeshPolygons[i].isNavigable) {
            //     debugGraphics.fillStyle(0x00FF00, 0.);
            // } else {
            //     debugGraphics.fillStyle(0xFF0000, 0.);
            // }
            debugGraphics.fillStyle(0xF0FFFF, 0.2);

            debugGraphics.lineStyle(1, 0xff0000, 1.0);
            debugGraphics.fillPoints(cartPoly.getCartesianPolygon().points, true);
            debugGraphics.strokePoints(cartPoly.getCartesianPolygon().points, true);
        }

        const debugGraphics2 = this.add.graphics().setAlpha(1);
        debugGraphics2.setDepth(100000000);
        this.navMeshGrid.phaserNavMesh.enableDebug(debugGraphics2);
        this.navMeshGrid.phaserNavMesh.debugDrawClear(); // Clears the overlay
        // Visualize the underlying navmesh
        this.navMeshGrid.phaserNavMesh.debugDrawMesh({
            drawCentroid: false,
            drawBounds: false,
            drawNeighbors: true,
            drawPortals: false
        });
    }
}
