import Phaser from 'phaser';
import { PhaserNavMesh } from "phaser-navmesh/src";
import { Room } from "@/entities/Room";
import { Equipment } from "@/entities/Equipment";

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


import character from "@/game/assets/images/characters.png";
import CharacterObject from "@/game/objects/characterObject";
import DoorGroundObject from "@/game/objects/doorGroundObject";
import DoorObject from "@/game/objects/doorObject";
import InteractObject from "@/game/objects/interactObject";
import DecorationObject from "../objects/decorationObject";
import ShelfObject from "@/game/objects/shelfObject";
import { Door as DoorEntity } from "@/entities/Door";

import laboratory from "@/game/assets/mush_lab.json";
import medlab from "@/game/assets/mush_medlab.json";
import central_corridor from "@/game/assets/central_corridor.json";
import front_storage from "@/game/assets/front_storage.json";
import front_corridor from "@/game/assets/front_corridor.json";


import fire_particles_frame from "@/game/assets/images/fire_particles.json";
import fire_particles from "@/game/assets/images/fire_particles.png";
import smoke_particle from "@/game/assets/images/smoke_particle.png";


import { PhaserNavMeshPlugin } from "phaser-navmesh/src/index";
import OutlinePostFx from 'phaser3-rex-plugins/plugins/outlinepipeline.js';

import { Player } from "@/entities/Player";
import PlayableCharacterObject from "@/game/objects/playableCharacterObject";
import { IsometricCoordinates, CartesianCoordinates } from "@/game/types";
import EquipmentObject from "@/game/objects/equipmentObject";
import IsometricGeom from "@/game/scenes/isometricGeom";
import { SceneGrid } from "@/game/scenes/sceneGrid";

export default class DaedalusScene extends Phaser.Scene
{
    public navMesh : PhaserNavMesh;

    private navMeshPlugin!: PhaserNavMeshPlugin;
    private layer : Phaser.Tilemaps.TilemapLayer | null;
    private playerSprite! : PlayableCharacterObject;
    private player : Player;
    private navMeshPolygons : Array<Array<{ x: number, y: number }>>;
    private characterSize : number;
    private room : Room;
    private cameraTarget : { x : number, y : number};
    private groups: Array<Phaser.GameObjects.Group>;
    public sceneGrid: SceneGrid;

    public selectedGameObject : Phaser.GameObjects.GameObject | null;

    constructor(player: Player) {
        super('game-scene');

        this.navMeshPolygons = [];
        this.characterSize = 8;

        if (player.room === null){
            throw new Error('player should have a room');
        }

        this.room = player.room;
        this.player = player;

        this.navMesh = new PhaserNavMesh(this.navMeshPlugin, this, 'pathfinding', this.navMeshPolygons);
        this.sceneGrid = new SceneGrid(this);
        this.layer = null;

        this.selectedGameObject = null;

        this.cameraTarget = { x: 0 , y: 0 };
        this.groups = [];
    }


    preload(): void
    {
        this.load.tilemapTiledJSON('medlab', medlab);
        this.load.tilemapTiledJSON('laboratory', laboratory);
        this.load.tilemapTiledJSON('central_corridor', central_corridor);
        this.load.tilemapTiledJSON('front_storage', front_storage);
        this.load.tilemapTiledJSON('front_corridor', front_corridor);


        this.load.image('ground_tileset', ground_tileset);
        this.load.image('wall_tileset', wall_tileset);


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
        this.load.spritesheet('beds_object', beds_object, { frameHeight: 60, frameWidth: 67 });
        this.load.spritesheet('door_ground_tileset', door_ground_tileset, { frameHeight: 36, frameWidth: 64 });
        this.load.spritesheet('chair_object', chair_object, { frameHeight: 36, frameWidth: 34 });
        this.load.spritesheet('door_object', door_object, { frameHeight: 73, frameWidth: 48 });
        this.load.spritesheet('neron_terminal_object', neron_object, { frameHeight: 64, frameWidth: 41 });
        this.load.spritesheet('shelf_object', shelf_object, { frameHeight: 42, frameWidth: 35 });
        this.load.spritesheet('papers', papers, { frameHeight: 12, frameWidth: 16 });
        this.load.spritesheet('broom', broom, { frameHeight: 29, frameWidth: 17 });
        this.load.spritesheet('shelf_front_storage1', shelf_front_storage1, { frameHeight: 101, frameWidth: 123 });
        this.load.spritesheet('shelf_front_storage2', shelf_front_storage2, { frameHeight: 91, frameWidth: 111 });
        this.load.spritesheet('shelf_front_storage3', shelf_front_storage3, { frameHeight: 74, frameWidth: 109 });
        this.load.spritesheet('shelf_front_storage4', shelf_front_storage4, { frameHeight: 79, frameWidth: 109 });
        this.load.spritesheet('transparent_wall_object', transparent_wall_object, { frameHeight: 69, frameWidth: 54 });


        this.load.spritesheet('ground_object', ground_tileset, { frameHeight: 72, frameWidth: 32 });

        this.load.image('smoke_particle', smoke_particle);
        this.load.atlas('fire_particles', fire_particles, fire_particles_frame);
    }

    // eslint-disable-next-line no-unused-vars
    create(): void
    {
        const map = this.add.tilemap(this.room.key);

        const IsoTileSize = 16;


        const tilesets = [
            map.addTilesetImage('ground_tileset', 'ground_tileset'),
            map.addTilesetImage('wall_tileset', 'wall_tileset')
        ];


        // ground and wall tilesets are aligned to their top left, contrary to objects,
        // tilemaps must be then shifted to set the center of the ground tile (only the 32 x 32 at the bottom)
        const tilemapsShift = new CartesianCoordinates(- 16, -(72 - 16));
        const objectsShift = new CartesianCoordinates(0, 0);



        const groundTilesThickness = 4;
        const globalPolygon = [
            { x: 2 * IsoTileSize - groundTilesThickness + this.characterSize, y: 2 * IsoTileSize - groundTilesThickness + this.characterSize },
            { x: (map.width - 2) * IsoTileSize- groundTilesThickness - this.characterSize, y: 2 * IsoTileSize + groundTilesThickness- this.characterSize },
            { x: (map.width - 2) * IsoTileSize- groundTilesThickness - this.characterSize, y: (map.height - 2) * IsoTileSize - groundTilesThickness - this.characterSize },
            { x: 2 * IsoTileSize- groundTilesThickness + this.characterSize, y: (map.height - 2) * IsoTileSize- groundTilesThickness- this.characterSize }
        ];

        this.sceneGrid.addSceneGeom([new IsometricGeom(
            new IsometricCoordinates(map.width * IsoTileSize/2, map.height * IsoTileSize/2),
            new IsometricCoordinates((map.width - 4) * IsoTileSize, (map.height - 4) * IsoTileSize))
        ]);

        this.navMeshPolygons.push(globalPolygon);


        (<Phaser.Renderer.WebGL.WebGLRenderer>this.game.renderer).pipelines.addPostPipeline('outline', OutlinePostFx );


        for (let i=0; i < map.layers.length; i++) {
            const buildingLayer = map.layers[i];

            if (buildingLayer.name === 'wall') {
                const wallLayer = map.createLayer(i, tilesets, tilemapsShift.x, tilemapsShift.y);
                wallLayer.setDepth(this.computeFixedDepth(this.getCustomPropertyByName(buildingLayer, 'depth')));

            } else if (buildingLayer.name === 'ground') {
                const groundLayer = map.createLayer(i, tilesets, tilemapsShift.x, tilemapsShift.y);
                groundLayer.setDepth(this.computeFixedDepth(this.getCustomPropertyByName(buildingLayer, 'depth')));
            }
        }

        this.createFromTiledObject(map.getObjectLayer('doors'), this, map.tilesets, objectsShift, this.room);
        this.createFromTiledObject(map.getObjectLayer('objects'), this, map.tilesets, objectsShift, this.room);


        this.sceneGrid.updateDepth();
        this.navMesh = new PhaserNavMesh(this.navMeshPlugin, this, 'pathfinding', this.navMeshPolygons, 0);


        /*  // navMesh Debug
        const debugGraphics = this.add.graphics().setAlpha(1);
        for (let i = 0; i < this.navMeshPolygons.length; i++) {
            const polygon = this.navMeshPolygons[i];

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
            debugGraphics.fillStyle(0x00ff08, 0.5);
            debugGraphics.lineStyle(1, 0x000000, 1.0);
            debugGraphics.fillPoints(cartPoly.getCartesianPolygon().points, true);
            debugGraphics.strokePoints(cartPoly.getCartesianPolygon().points, true);
        }*/

        // const debugGraphics2 = this.add.graphics().setAlpha(1);
        // this.navMesh.enableDebug(debugGraphics2);
        // this.navMesh.debugDrawClear(); // Clears the overlay
        // // Visualize the underlying navmesh
        // this.navMesh.debugDrawMesh({
        //     drawCentroid: true,
        //     drawBounds: false,
        //     drawNeighbors: true,
        //     drawPortals: true
        // });


        //place the starting camera.
        //If the scene size is larger than the camera, the camera is centered on the player
        //else it is centered on the scene
        const playerCoordinates = this.getPlayerCoordinates();

        const cameraWidth = 424;
        const cameraHeight = 560;
        const sceneCartWidth = (map.width + map.height) * IsoTileSize;
        const sceneCartHeight = (map.width + map.height) * IsoTileSize/2; //72 is wall height

        const wallHeight = 72;
        this.cameras.main.setBounds(-map.height*IsoTileSize, -wallHeight, sceneCartWidth , sceneCartHeight + wallHeight);


        this.input.setTopOnly(true);
        this.input.setGlobalTopOnly(true);


        this.createPlayers(playerCoordinates);

        this.cameras.main.startFollow(this.playerSprite);


        this.input.on('gameobjectdown', (pointer: Phaser.Input.Pointer, gameObject: InteractObject, event: any) => {
            if (this.selectedGameObject !== null &&
                this.selectedGameObject instanceof InteractObject &&
                this.selectedGameObject !== gameObject
            ) {
                this.selectedGameObject.onClickedOut();
                this.selectedGameObject = gameObject;
            }
            if (gameObject instanceof InteractObject){
                gameObject.onSelected();
                this.selectedGameObject = gameObject;
            }
            if (!(gameObject instanceof DoorObject)) {
                event.stopPropagation();
            }
        });


        if (this.room.isOnFire) {
            this.displayFire(map.getLayer('ground'));
        }


        /*console.log(this.sceneGrid);
        // debug scene grid
        console.log(this.sceneGrid.polygonArray.length);
        const debugGraphics = this.add.graphics().setAlpha(1);
        for (let i = 0; i < this.sceneGrid.polygonArray.length; i++) {
            const polygon = this.sceneGrid.polygonArray[i].geom;


            const cartPoly = polygon.getCartesianPolygon();
            debugGraphics.fillStyle(0x00ff08, 0.1);
            debugGraphics.setDepth(10000000);
            debugGraphics.lineStyle(1, 0x000000, 1.0);
            debugGraphics.fillPoints(cartPoly.points, true);
            debugGraphics.strokePoints(cartPoly.points, true);
        }*/
    }

    displayFire(layer: Phaser.Tilemaps.LayerData): void
    {
        for (let i = 1; i < layer.data.length; i++) {
            const test = layer.data[i];
            for (let j = 1; j < test.length; j++) {
                if (test[j].index !== -1) {
                    //is the tile on fire
                    if (Math.random() < 0.2) {
                        //intensity of fire
                        const tileCoordinates = new IsometricCoordinates(8 + i*16, 8 + (j+1) * 16);
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

    update (time: number, delta: number): void
    {
        this.playerSprite.update();
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
        const randomPoly = this.navMeshPolygons[Math.floor(Math.random() * this.navMeshPolygons.length)];

        const polyExtremum = this.getPolygonExtremum(randomPoly);

        const randomIsoCord = new IsometricCoordinates(
            polyExtremum.min.x + Math.random() * (polyExtremum.max.x - polyExtremum.min.x),
            polyExtremum.min.y + Math.random() * (polyExtremum.max.y - polyExtremum.min.y)
        );

        const cartCoords = randomIsoCord.toCartesianCoordinates();

        //Coordinates of player in the navMesh is given relative to the feet of the player
        //Coordinates given in the constructor of player are the center of the sprite
        cartCoords.setTo(cartCoords.x, cartCoords.y - 16);

        return cartCoords;
    }

    createFromTiledObject(
        objectLayer: Phaser.Tilemaps.ObjectLayer,
        scene: Phaser.Scene,
        tilesets: Array<Phaser.Tilemaps.Tileset>,
        shift: CartesianCoordinates,
        room: Room
    ): Array<Phaser.GameObjects.GameObject>
    {
        const results = [];

        const objects = objectLayer.objects;

        //loop for each tiled object
        for (let i = 0; i < objects.length; i++) {
            const obj = objects[i];


            if (obj.gid === undefined){
                throw new Error(obj.name + "gid is not defined");
            }

            const tileset = this.getTileset(tilesets, obj.gid);

            //if the equipment is present according to the API
            if (!(obj.type === 'interact' && room.equipments.filter(function (equipment: Equipment) {return equipment.key === obj.name;}).length === 0)){
                const newObject = this.createPhaserObject(obj, tileset, shift);
                results.push(newObject);

                // some equipment have depth already fixed (stuff on the wall, doors, flat things on the ground)
                const fixedDepth = this.getCustomPropertyByName(obj, 'depth');
                if (fixedDepth !== -1) {
                    newObject.setDepth(this.computeFixedDepth(fixedDepth));
                }

                if (obj.type !== 'door' && obj.type !== 'door_ground' &&
                    (fixedDepth === -1 || this.isCustomPropertyByName(obj, 'collides'))
                ) {
                    this.sceneGrid.addObject(newObject);
                }
            }
        }

        return results;
    }

    computeFixedDepth(tiledDepth: number): number
    {
        return tiledDepth + 5;
    }


    // This function extract the tileset corresponding to a given gid
    // (is gid comprised between first gid of this tileset and the first gid of next tileset)
    getTileset(tilesets: any, gid: number): any
    {
        let chosenTileset = tilesets[0];

        for (let i = 1; i < tilesets.length; i++) {

            const tileset = tilesets[i];

            if (((gid - tileset.firstgid) < (gid - chosenTileset.firstgid)) &&
                (tileset.firstgid <= gid))
            {
                chosenTileset = tileset;
            }
        }

        return chosenTileset;
    }


    createPhaserObject(
        obj: Phaser.Types.Tilemaps.TiledObject,
        tileset: Phaser.Tilemaps.Tileset,
        shift: CartesianCoordinates,
    ): DecorationObject
    {
        //object coordinates are stored in tiled in iso coords
        //to correctly place them in phaser we need the cartesian coordinates
        const cart_coords = this.getObjectCartesianCoordinates(obj, shift);

        if (obj.gid === undefined){
            throw new Error(obj.name + "gid is not defined");
        }
        const frame = obj.gid - tileset.firstgid;
        const name = obj.name;

        if (this.isCustomPropertyByName(obj, 'collides')){
            this.updateNavMesh(obj);
        }

        const equipmentEntity = this.getEquipmentFromTiledObject(obj);
        const group = this.getGroupOfObject(obj, equipmentEntity);
        const isAnimationYoyo = this.isCustomPropertyByName(obj, 'animationYoyo');

        switch (obj.type)
        {
        case 'decoration':
            return new DecorationObject(this, cart_coords, this.getIsometricGeom(obj), tileset, frame, name, isAnimationYoyo);
        case 'door':
            if (equipmentEntity instanceof DoorEntity) {
                const newDoor = new DoorObject(this, cart_coords, this.getIsometricGeom(obj), tileset, frame, equipmentEntity);
                newDoor.setDepth(this.getCustomPropertyByName(obj, 'depth') + 5);
                return newDoor;
            }
        case 'door_ground':
            if (equipmentEntity instanceof DoorEntity) {
                return new DoorGroundObject(this, cart_coords, this.getIsometricGeom(obj), tileset, frame, equipmentEntity);
            }
        case 'interact':
            if (equipmentEntity instanceof Equipment) {
                return new EquipmentObject(this, cart_coords, this.getIsometricGeom(obj), tileset, frame, equipmentEntity, isAnimationYoyo, group);
            }
        case 'shelf':
            return new ShelfObject(this, cart_coords, this.getIsometricGeom(obj), tileset, frame, name, isAnimationYoyo, group);
        }
        throw new Error(obj.name + "type does not exist");
    }

    getEquipmentFromTiledObject(obj: Phaser.Types.Tilemaps.TiledObject): DoorEntity | Equipment | undefined
    {
        switch (obj.type)
        {
        case 'door':
        case 'door_ground':
            return this.room.doors.find((door: DoorEntity) => {
                return (door.key === obj.name);
            });
        case 'interact':
            return this.room.equipments.find((equipment: Equipment) => (equipment.key === obj.name));
        }

        return undefined;
    }

    getGroupOfObject(obj: Phaser.Types.Tilemaps.TiledObject, equipmentEntity: DoorEntity | Equipment | undefined): Phaser.GameObjects.Group | null
    {
        if ( !this.isCustomPropertyByName(obj, 'grouped') ) {
            return null;
        } else {
            let filteredGroups: Array<Phaser.GameObjects.Group> = [];
            const groupName = this.getGroupName(obj);

            switch (obj.type)
            {
            case 'door':
            case 'door_ground':
            case 'interact':
            case 'shelf':
                filteredGroups = this.groups.filter((group: Phaser.GameObjects.Group) => {
                    return group.name === groupName;
                });
                break;
            }
            if (filteredGroups.length === 1) {
                return filteredGroups[0];
            } else {
                const group = this.add.group(undefined, { name: groupName });
                this.groups.push(group);
                return group;
            }
        }
    }

    getGroupName(obj: Phaser.Types.Tilemaps.TiledObject): string
    {
        switch (obj.type)
        {
        case 'door':
        case 'door_ground':
        case 'interact':
            return obj.name;
        case 'shelf':
            return 'shelf';
        }

        return 'undefined';
    }


    //this function modifies the pathfinding grid each time a new object is added
    updateNavMesh(obj: Phaser.Types.Tilemaps.TiledObject): void
    {
        //get object coordinates
        const isoGeom = this.getIsometricGeom(obj).enlargeGeom(this.characterSize);

        const objSize = { max: isoGeom.getMaxIso(), min: isoGeom.getMinIso() };


        const newNavMeshPolys = [];


        //Loop through the current navMesh polygons
        for (let i = 0; i < this.navMeshPolygons.length; i++) {
            //Check if this object is within existing polygon
            const polyExtremum = this.getPolygonExtremum(this.navMeshPolygons[i]);

            if (this.isInPolygon(polyExtremum, objSize)) {
                //if the object is within the polygon we need to cut this polygon into 2 to 3 new polygons
                const maxX = Math.min(objSize.max.x, polyExtremum.max.x);
                const maxY = Math.min(objSize.max.y, polyExtremum.max.y);
                const minX = Math.max(objSize.min.x, polyExtremum.min.x);
                const minY = Math.max(objSize.min.y, polyExtremum.min.y);

                if (polyExtremum.min.y < objSize.min.y) {
                    newNavMeshPolys.push([
                        { x: polyExtremum.min.x, y: polyExtremum.min.y },
                        { x: polyExtremum.min.x, y: objSize.min.y },
                        { x: maxX, y: objSize.min.y },
                        { x: maxX, y: polyExtremum.min.y }
                    ]);
                }

                if (polyExtremum.max.x > objSize.max.x) {
                    newNavMeshPolys.push([
                        { x: objSize.max.x, y: polyExtremum.min.y },
                        { x: polyExtremum.max.x, y: polyExtremum.min.y },
                        { x: polyExtremum.max.x, y: maxY },
                        { x: objSize.max.x, y: maxY }
                    ]);
                }

                if (polyExtremum.max.y > objSize.max.y) {
                    newNavMeshPolys.push([
                        { x: minX, y: objSize.max.y },
                        { x: polyExtremum.max.x, y: objSize.max.y },
                        { x: polyExtremum.max.x, y: polyExtremum.max.y },
                        { x: minX, y: polyExtremum.max.y }
                    ]);
                }

                if (polyExtremum.min.x < objSize.min.x) {
                    newNavMeshPolys.push([
                        { x: polyExtremum.min.x, y: minY },
                        { x: objSize.min.x, y: minY },
                        { x: objSize.min.x, y: polyExtremum.max.y },
                        { x: polyExtremum.min.x, y: polyExtremum.max.y }
                    ]);
                }

                delete this.navMeshPolygons[i];
            } else {
                newNavMeshPolys.push(this.navMeshPolygons[i]);
            }
        }

        // once we checked all polygons we replace the old grid by the new one
        this.navMeshPolygons = newNavMeshPolys;
    }

    isInPolygon(polyExtremum: {max: IsometricCoordinates, min: IsometricCoordinates}, objSize: {max: IsometricCoordinates, min: IsometricCoordinates}): boolean
    {
        return polyExtremum.min.x < objSize.max.x && polyExtremum.max.x > objSize.min.x &&
            polyExtremum.min.y < objSize.max.y && polyExtremum.max.y > objSize.min.y;
    }


    getPolygonExtremum(polygon: Array<{ x: number, y: number }>): { max: IsometricCoordinates, min: IsometricCoordinates }
    {
        const min = new IsometricCoordinates(polygon[0].x, polygon[0].y);
        const max = new IsometricCoordinates(polygon[0].x, polygon[0].y);

        for (let i = 1; i < polygon.length; i++) {
            if (polygon[i].x > max.x) {
                max.x = polygon[i].x;
            } else if (polygon[i].x < min.x) {
                min.x = polygon[i].x;
            }

            if (polygon[i].y > max.y) {
                max.y = polygon[i].y;
            } else if (polygon[i].y < min.y) {
                min.y = polygon[i].y;
            }
        }

        return { min: min, max: max };
    }


    //tiled object coordinates in the isometric frame
    //This function computes cartesian coordinates for the object
    getObjectCartesianCoordinates(obj: Phaser.Types.Tilemaps.TiledObject, shift: CartesianCoordinates): CartesianCoordinates
    {
        if (obj.x === undefined || obj.y === undefined  || obj.width === undefined || obj.height === undefined){
            throw new Error('coordinates should be provided');
        }

        const isoCoords = new IsometricCoordinates(obj.x, obj.y);
        const cartCoords = isoCoords.toCartesianCoordinates();

        //The tiled coordinates should be given relative to the bottom left of the object
        //hence this shift of half the size of the sprite
        return new CartesianCoordinates(cartCoords.x + shift.x, cartCoords.y + shift.y);
    }


    //Isometric size of the object is stored as a custom property of the object
    getObjectIsoSize(obj: Phaser.Types.Tilemaps.TiledObject): IsometricCoordinates
    {
        const isoSize = new IsometricCoordinates(0,0);
        for (let i = 0; i < obj.properties.length; i++) {
            if (obj.properties[i].name === 'isoSizeX') {
                isoSize.setTo(obj.properties[i].value, isoSize.y);

            } else if (obj.properties[i].name === 'isoSizeY') {
                isoSize.setTo(isoSize.x, obj.properties[i].value);
            }
        }
        return isoSize;
    }

    isCustomPropertyByName(obj: Phaser.Types.Tilemaps.TiledObject, property: string): boolean
    {
        const existingKeys = ['grouped', 'collides', 'animationYoyo'];
        if (existingKeys.includes(property)) {
            for (let i = 0; i < obj.properties.length; i++) {
                if (obj.properties[i].name === property) {
                    return obj.properties[i].value;
                }
            }
        }
        return false;
    }

    getCustomPropertyByName(obj: Phaser.Types.Tilemaps.TiledObject | Phaser.Types.Tilemaps.ObjectLayerConfig, property: string): number
    {
        const existingKeys = ['depth'];
        if (existingKeys.includes(property)) {
            for (let i = 0; i < obj.properties.length; i++) {
                if (obj.properties[i].name === property) {
                    return obj.properties[i].value;
                }
            }
        }
        return -1;
    }


    //bounding box of the object is not really fit to the object (due to isometric projection)
    //let compute the coordinates of the bottom left of the object in isometric coordinates
    //to do this we use the isoSizeX and isoSizeY custom properties of the object
    getIsometricGeom(obj: Phaser.Types.Tilemaps.TiledObject): IsometricGeom
    {
        if (obj.x === undefined || obj.y === undefined || obj.height === undefined){
            throw new Error('coordinates should be provided');
        }

        const isoSize = this.getObjectIsoSize(obj);

        const CartCoords = (new IsometricCoordinates(obj.x, obj.y)).toCartesianCoordinates();

        //The center of the isometric shape is different from the center of the sprite (i.e. we need to remove the height part of the object
        const cartGroundCenter = new CartesianCoordinates(CartCoords.x, CartCoords.y + obj.height/2 - (isoSize.x + isoSize.y)/4);

        const isoCoords = cartGroundCenter.toIsometricCoordinates();

        return new IsometricGeom(isoCoords, isoSize);
    }
}
