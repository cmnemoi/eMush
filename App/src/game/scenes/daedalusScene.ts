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


import { PhaserNavMeshPlugin } from "phaser-navmesh/src/index";
import { Player } from "@/entities/Player";
import PlayableCharacterObject from "@/game/objects/playableCharacterObject";
import { IsometricCoordinates, CartesianCoordinates, CartesianDistance, IsometricDistance, toCartesianCoords } from "@/game/types";

export default class DaedalusScene extends Phaser.Scene
{
    public navMesh : PhaserNavMesh;

    private navMeshPlugin!: PhaserNavMeshPlugin;
    private layer : Phaser.Tilemaps.TilemapLayer | null;
    private playerSprite! : PlayableCharacterObject;
    private player : Player;
    private navMeshPolygons : Array<Array<IsometricCoordinates>>;
    private characterSize : number;
    private room : Room;

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
        this.layer = null;
    }


    preload(): void
    {
        this.load.tilemapTiledJSON('medlab', medlab);
        this.load.tilemapTiledJSON('laboratory', laboratory);
        this.load.tilemapTiledJSON('central_corridor', central_corridor);


        this.load.image('ground_tileset', ground_tileset);
        this.load.image('wall_tileset', wall_tileset);


        this.load.spritesheet('character', character, { frameHeight: 48, frameWidth: 32 });

        this.load.spritesheet('centrifuge_object', centrifuge_object, { frameHeight: 34, frameWidth: 30 });
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
        this.load.spritesheet('door_ground_object', door_ground_tileset, { frameHeight: 36, frameWidth: 64 });
        this.load.spritesheet('chair_object', chair_object, { frameHeight: 38, frameWidth: 36 });
        this.load.spritesheet('door_object', door_object, { frameHeight: 73, frameWidth: 48 });
        this.load.spritesheet('neron_terminal_object', neron_object, { frameHeight: 64, frameWidth: 41 });
        this.load.spritesheet('shelf_object', shelf_object, { frameHeight: 42, frameWidth: 35 });

        this.load.spritesheet('ground_object', ground_tileset, { frameHeight: 72, frameWidth: 32 });
        this.load.image('door_ground_tileset', door_ground_tileset);
    }

    // eslint-disable-next-line no-unused-vars
    create(): void
    {
        const map = this.add.tilemap(this.room.key);

        const IsoTileSize = 16;

        const sceneIsoSizeX = map.width * IsoTileSize;
        const sceneIsoSizeY = map.height * IsoTileSize;

        //this variable is used for depth sorting
        //max isoX and max isoY should be represented at the same depth layer
        const sceneAspectRatio: IsometricDistance = {
            x: Math.max(sceneIsoSizeX, sceneIsoSizeY) - sceneIsoSizeX,
            y: Math.max(sceneIsoSizeX, sceneIsoSizeY) - sceneIsoSizeY
        };

        const tilesets = [
            map.addTilesetImage('ground_tileset', 'ground_tileset'),
            map.addTilesetImage('wall_tileset', 'wall_tileset'),
            map.addTilesetImage('door_ground_tileset', 'door_ground_tileset')
        ];

        this.cameras.main.setBounds(-275, -125, 500, 500);


        //this shift is yet to be understood, but it is required to align tile layers with object layers
        const tileSize = 16;
        const magicalShift: CartesianDistance = { x: -tileSize, y: tileSize - 72 }; //Why ????


        const globalPolygon = [
            { x: 2 * IsoTileSize + this.characterSize, y: 2 * IsoTileSize + this.characterSize },
            { x: (map.width - 2) * IsoTileSize - this.characterSize, y: 2 * IsoTileSize + this.characterSize },
            { x: (map.width - 2) * IsoTileSize- this.characterSize, y: (map.height - 2) * IsoTileSize - this.characterSize },
            { x: 2 * IsoTileSize + this.characterSize, y: (map.height - 2) * IsoTileSize - this.characterSize }
        ];

        this.navMeshPolygons.push(globalPolygon);


        map.createLayer('wallBack', tilesets, magicalShift.x, magicalShift.y);

        this.createFromTiledObject(map.getObjectLayer('doors'), this, map.tilesets, { x: 0, y: 0 }, sceneAspectRatio, this.room);
        map.createLayer('wall', tilesets, magicalShift.x, magicalShift.y);
        this.layer = map.createLayer('ground', tilesets, magicalShift.x, magicalShift.y);
        this.layer.setInteractive();

        this.createFromTiledObject(map.getObjectLayer('objects'), this, map.tilesets, { x: 0, y: 0 }, sceneAspectRatio, this.room);

        this.navMesh = new PhaserNavMesh(this.navMeshPlugin, this, 'pathfinding', this.navMeshPolygons, 8);


        // const debugGraphics = this.add.graphics().setAlpha(1);
        // this.navMesh.enableDebug(debugGraphics);
        // this.navMesh.debugDrawClear(); // Clears the overlay
        // // Visualize the underlying navmesh
        // this.navMesh.debugDrawMesh({
        //     drawCentroid: true,
        //     drawBounds: false,
        //     drawNeighbors: true,
        //     drawPortals: true
        // });

        this.input.setTopOnly(true);
        this.input.setGlobalTopOnly(true);


        this.createPlayers(sceneAspectRatio);
    }

    update (time: number, delta: number): void
    {
        this.playerSprite.update();
    }

    createPlayers(sceneAspectRatio: CartesianDistance): void
    {
        this.playerSprite = new PlayableCharacterObject(
            this,
            this.getPlayerCoordinates(),
            sceneAspectRatio,
            this.player
        );

        this.room.players.forEach((roomPlayer: Player) => {
            if (roomPlayer.id !== this.player.id) {
                new CharacterObject(
                    this,
                    this.getPlayerCoordinates(),
                    sceneAspectRatio,
                    roomPlayer
                );
            }
        });
    }

    getPlayerCoordinates(): CartesianCoordinates
    {
        const randomPoly = this.navMeshPolygons[Math.floor(Math.random() * this.navMeshPolygons.length)];

        const polyExtremum = this.getPolygonExtremum(randomPoly);

        const randomX = polyExtremum.min.x + Math.random() * (polyExtremum.max.x - polyExtremum.min.x);
        const randomY = polyExtremum.min.y + Math.random() * (polyExtremum.max.y - polyExtremum.min.y);

        const cartCoords = toCartesianCoords({ x : randomX, y: randomY });

        //Coordinates of player in the navMesh is given relative to the feet of the player
        //Coordinates given in the constructor of player are the center of the sprite
        cartCoords.y = cartCoords.y - 24;

        return cartCoords;
    }

    createFromTiledObject(
        objectLayer: Phaser.Tilemaps.ObjectLayer,
        scene: Phaser.Scene,
        tilesets: Array<Phaser.Tilemaps.Tileset>,
        shift: CartesianDistance,
        sceneAspectRatio: IsometricDistance,
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
                results.push(this.createPhaserObject(obj, tileset, shift, sceneAspectRatio));
            }
        }

        return results;
    }


    //This function extract the tileset corresponding to a given gid
    // //(is gid comprised between first gid of this tileset and the first gid of next tileset)
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
        shift: CartesianDistance,
        sceneAspectRatio: IsometricDistance
    ): Phaser.GameObjects.GameObject
    {
        //object coordinates are stored in tiled in iso coords
        //to correctly place them in phaser we need the cartesian coordinates
        const cart_coords = this.getObjectCartesianCoordinates(obj, shift);

        if (obj.gid === undefined){
            throw new Error(obj.name + "gid is not defined");
        }
        const frame = obj.gid - tileset.firstgid;
        const name = obj.name;


        if (this.isObjectCollision(obj)){
            this.updateNavMesh(obj);
        }

        switch (obj.type)
        {
        case 'decoration':
            return new DecorationObject(this, cart_coords, this.getIsoCenter(obj), tileset, frame, name, sceneAspectRatio);

        case 'door':
            // @ts-ignore
            const currentDoor = this.room.doors.find((door: DoorEntity) => (door.key === obj.name));
            if (typeof currentDoor !== "undefined") {
                return new DoorObject(this, cart_coords, frame, currentDoor);
            }

        case 'door_ground':
            const currentDoorGround = this.room.doors.find((door: DoorEntity) => {
                return (door.key === obj.name);
            });
            if (typeof currentDoorGround !== "undefined") {
                return new DoorGroundObject(this, cart_coords, frame, currentDoorGround);
            }
        case 'interact':
            const currentEquipment = this.room.equipments.find((equipment: Equipment) => (equipment.key === obj.name));
            if (typeof currentEquipment !== "undefined") {
                return new InteractObject(this, cart_coords, this.getIsoCenter(obj), tileset, frame, currentEquipment, sceneAspectRatio);
            }

        case 'shelf':
            this.updateNavMesh(obj);
            return new ShelfObject(this, cart_coords, this.getIsoCenter(obj), tileset, frame, name, sceneAspectRatio);
        }

        throw new Error(obj.name + "does not exist");
    }


    //this function modifies the pathfinding grid each time a new object is added
    updateNavMesh(obj: Phaser.Types.Tilemaps.TiledObject): void
    {
        //get object coordinates
        const isoCoords = this.getIsoCenter(obj);
        const isoSize = this.getObjectIsoSize(obj);


        const objSize = {
            max: {
                x: isoCoords.x + isoSize.x/2 + this.characterSize,
                y: isoCoords.y + isoSize.y/2 + this.characterSize
            },
            min: {
                x: isoCoords.x - isoSize.x/2 - this.characterSize,
                y: isoCoords.y - isoSize.y/2 - this.characterSize
            }
        };


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


    getPolygonExtremum(polygon: Array<IsometricCoordinates>): { max: IsometricCoordinates, min: IsometricCoordinates }
    {
        const min = { x: polygon[0].x, y: polygon[0].y };
        const max = { x: polygon[0].x, y: polygon[0].y };

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


    //tiled object coordinates are isometric
    //This function computes cartesian coordinates for the object
    getObjectCartesianCoordinates(obj: Phaser.Types.Tilemaps.TiledObject, shift: CartesianCoordinates): CartesianCoordinates
    {
        if (obj.x === undefined || obj.y === undefined  || obj.width === undefined || obj.height === undefined){
            throw new Error('coordinates should be provided');
        }

        const cartCoords = toCartesianCoords({ x: obj.x, y: obj.y });

        //The tiled coordinates should be given relative to the bottom left of the object
        //hence this shift of half the size of the sprite
        return { x : cartCoords.x + obj.width/2 + shift.x, y: cartCoords.y - obj.height/2 + shift.y };
    }


    //Isometric size of the object is stored as a custom property of the object
    getObjectIsoSize(obj: Phaser.Types.Tilemaps.TiledObject): IsometricDistance
    {
        const isoSize = { x: 0, y: 0 };
        for (let i = 0; i < obj.properties.length; i++) {
            if (obj.properties[i].name === 'isoSizeX') {
                isoSize.x = obj.properties[i].value;
            } else if (obj.properties[i].name === 'isoSizeY') {
                isoSize.y = obj.properties[i].value;
            }
        }
        return isoSize;
    }

    //collision property of the object is stored in a custom property
    isObjectCollision(obj: Phaser.Types.Tilemaps.TiledObject): boolean
    {
        for (let i = 0; i < obj.properties.length; i++) {
            if (obj.properties[i].name === 'collides') {
                return obj.properties[i].value;
            }
        }

        return false;
    }

    //bounding box of the object is not really fit to the object (due to isometric projection)
    //let compute the coordinates of the bottom left of the object in isometric coordinates
    //to do this we use the isoSizeX and isoSizeY custom properties of the object
    getIsoCenter(obj: Phaser.Types.Tilemaps.TiledObject): IsometricCoordinates
    {
        if (obj.x === undefined || obj.y === undefined){
            throw new Error('coordinates should be provided');
        }

        //first compute cartesian coords (because the bounding box of the objet have a cartesian shape
        const cartBoundingX = (obj.x - obj.y);
        const cartBoundingY = (obj.x + obj.y)/2;

        //no lets get the cart coords of the true bottom left of the object (only y changes)
        const isoSize = this.getObjectIsoSize(obj);


        const cartObjectY = cartBoundingY - isoSize.x/2;
        //now get back to isoCoords...
        return { x: (cartObjectY + cartBoundingX/2) + isoSize.x/2, y: (cartObjectY - cartBoundingX/2) - isoSize.y/2 };
    }
}
