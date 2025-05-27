import Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import { SceneGrid } from "@/game/scenes/sceneGrid";
import MushTiledLayer from "@/game/tiled/mushTiledLayer";
import EquipmentObject from "@/game/objects/equipmentObject";
import MushTiledObject from "@/game/tiled/mushTiledObject";
import { Room } from "@/entities/Room";

export default class MushTiledMap {
    // ground and wall tilesets are aligned to their top left, contrary to objects,
    // tilemaps must be then shifted to set the center of the ground tile (only the 32 x 32 at the bottom)
    private tilemapsShift = new CartesianCoordinates(- 16, -(72 - 16));
    private objectsShift = new CartesianCoordinates(0, 0);
    private groundTilesThickness = 4;
    private isoTileSize = 16;
    private wallHeight = 72;

    public tilemap: Phaser.Tilemaps.Tilemap;
    public scene: DaedalusScene;
    private groups: Array<Phaser.GameObjects.Group>;
    private readonly equipments: Array<EquipmentObject>;

    constructor(scene: DaedalusScene, key: string) {
        this.tilemap = scene.add.tilemap(key);
        this.scene = scene;

        this.groups = [];
        this.equipments = [];

        this.tilemap.addTilesetImage('ground_tileset', 'ground_tileset');
        this.tilemap.addTilesetImage('wall_tileset', 'wall_tileset');
    }

    createInitialSceneGrid(sceneGrid: SceneGrid): SceneGrid
    {
        const sceneIsoSize = this.getMapSize();
        sceneGrid.addSceneGeom(sceneIsoSize, this.groundTilesThickness);

        return sceneGrid;
    }

    getMapSize(): IsometricCoordinates
    {
        return new IsometricCoordinates(this.tilemap.width * this.isoTileSize, this.tilemap.height * this.isoTileSize);
    }

    createLayers(room: Room, sceneGrid: SceneGrid ): void
    {
        for (let i=0; i < this.tilemap.layers.length; i++) {
            const tiledLayer = new MushTiledLayer(this.tilemap.layers[i]);

            if (tiledLayer.name === 'wall') {
                const wallLayer = this.tilemap.createLayer(i, this.tilemap.tilesets, this.tilemapsShift.x, this.tilemapsShift.y);
                if (wallLayer === null) {return;}

                wallLayer.setDepth(this.computeFixedDepth(tiledLayer.getCustomPropertyByName('depth')));

            } else if (tiledLayer.name === 'ground') {
                //ground layers needs to be rectangular
                const groundLayer = this.tilemap.createLayer(i, 'ground_tileset', this.tilemapsShift.x, this.tilemapsShift.y);
                if (groundLayer === null) {return;}

                const depth = tiledLayer.getCustomPropertyByName('depth');
                const walkingDepth = tiledLayer.getCustomPropertyByName('walkingDepth');

                groundLayer.setDepth(this.computeFixedDepth(depth));

                if (walkingDepth !== -1) {
                    sceneGrid.addGroundGeom(groundLayer.layer, this.groundTilesThickness, this.computeFixedDepth(walkingDepth));
                }
            }
        }
        sceneGrid.finalizeGroundMesh();
    }

    createEquipmentLayers(room: Room, sceneGrid: SceneGrid ): Array<EquipmentObject>
    {
        for (let i=0; i < this.tilemap.objects.length; i++) {
            this.createObjectsLayer(room, this.tilemap.objects[i], sceneGrid);
        }

        return this.equipments;
    }


    createObjectsLayer(
        room: Room,
        objectLayer: Phaser.Tilemaps.ObjectLayer,
        sceneGrid: SceneGrid
    ) {
        const addedObjectId: Array<number> = [];

        const objects = objectLayer.objects;

        //loop for each tiled object
        for (let i = 0; i < objects.length; i++) {
            const obj = new MushTiledObject(objects[i]);

            if (obj.tiledObj.gid === undefined){
                throw new Error(obj.tiledObj.name + "gid is not defined");
            }

            const tileset = obj.getTileset(this.tilemap.tilesets);
            const objEntity = obj.getEquipmentFromTiledObject(room, addedObjectId);

            //if the equipment is present according to the API
            if (!(obj.tiledObj.type === 'equipment' &&
                objEntity === undefined)
            ){
                const group = this.getGroupOfObject(obj);

                const newObject = obj.createPhaserObject(this.scene, tileset, this.objectsShift, objEntity, group);

                // some equipment have depth already fixed (stuff on the wall, doors, flat things on the ground)
                const fixedDepth = obj.getCustomPropertyByName('depth');
                const isCollision = obj.isCustomPropertyByName('collides');

                if (fixedDepth !== 0) {
                    newObject.setDepth(this.computeFixedDepth(fixedDepth));
                }

                if (isCollision || fixedDepth === 0) {
                    sceneGrid.addObject(newObject);
                }

                if (newObject instanceof EquipmentObject) {
                    addedObjectId.push(newObject.equipment.id);
                    this.equipments.push(newObject);
                }
            }
        }
    }

    getGroupOfObject(obj: MushTiledObject): Phaser.GameObjects.Group | null
    {
        if ( !obj.isCustomPropertyByName('grouped') ) {
            return null;
        } else {
            const groupName = obj.getGroupName();
            const filteredGroups: Array<Phaser.GameObjects.Group> = this.groups.filter((group: Phaser.GameObjects.Group) => {
                return group.name === groupName;
            });

            if (filteredGroups.length === 1) {
                return filteredGroups[0];
            } else {
                const group = this.scene.add.group(undefined, { name: groupName });
                this.groups.push(group);
                return group;
            }
        }
    }

    getCameraPosition(): { x: number, y: number, width: number, height: number}
    {
        const sceneCartWidth = (this.tilemap.width + this.tilemap.height) * this.isoTileSize;
        const sceneCartHeight = (this.tilemap.width + this.tilemap.height) * this.isoTileSize/2;

        return {
            x: -this.tilemap.height*this.isoTileSize,
            y: -this.wallHeight,
            width: sceneCartWidth,
            height: sceneCartHeight + this.wallHeight };
    }


    computeFixedDepth(tiledDepth: number): number
    {
        return tiledDepth*1000000 + 5;
    }


}
