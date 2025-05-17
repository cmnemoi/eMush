import Phaser from "phaser";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import IsometricGeom from "@/game/scenes/isometricGeom";
import { Room } from "@/entities/Room";
import { Equipment } from "@/entities/Equipment";
import { Door } from "@/entities/Door";
import EquipmentObject from "@/game/objects/equipmentObject";
import DecorationObject from "@/game/objects/decorationObject";
import DoorObject from "@/game/objects/doorObject";
import DoorGroundObject from "@/game/objects/doorGroundObject";
import ShelfObject from "@/game/objects/shelfObject";
import DaedalusScene from "@/game/scenes/daedalusScene";
import InteractObject, { InteractionInformation } from "@/game/objects/interactObject";
import PatrolShipObject from "@/game/objects/patrolShipObject";
import mushTextureProperties from "@/game/tiled/mushTextureProperties";
import { Skin } from "@/entities/Skin";

export default class MushTiledObject {
    public tiledObj: Phaser.Types.Tilemaps.TiledObject;
    public tileset: Phaser.Tilemaps.Tileset;

    constructor(
        tiledObj: Phaser.Types.Tilemaps.TiledObject,
        tilesets: Phaser.Tilemaps.Tileset[]
    ) {

        if (tiledObj.gid === undefined){
            throw new Error(tiledObj.name + "gid is not defined");
        }

        this.tiledObj = tiledObj;
        this.tileset = this.getTileset(tilesets);
    }

    getEquipmentFromTiledObject(room: Room, createdObjectId: Array<number>): Door | Equipment | undefined
    {
        const equipmentName = this.getCustomPropertyByName('gameEquipment');

        switch (this.tiledObj.type) {
        case 'door':
        case 'door_ground':
            return room.doors.find((door: Door) => {
                return (door.key === equipmentName);
            });
        case 'patrol_ship':
        case 'equipment':
            return room.equipments.find((equipment: Equipment) => (equipment.key === equipmentName &&
                (!(createdObjectId.includes(equipment.id)) || this.isCustomPropertyByName('grouped')))
            );
        }
        return undefined;
    }


    createPhaserObject(
        scene: DaedalusScene,
        equipmentEntity: Door | Equipment | undefined,
        skins: Skin[],
        group: Phaser.GameObjects.Group | null = null
    ): DecorationObject | undefined
    {
        //object coordinates are stored in tiled in iso coords
        //to correctly place them in phaser we need the cartesian coordinates
        const cart_coords = this.getObjectCartesianCoordinates();

        const isSkin = this.isCustomPropertyByName('skin');

        const textureProperties = new mushTextureProperties();
        textureProperties.setTexturesProperties(this.tiledObj, this.tileset, isSkin);

        for (let i = 0; i < skins.length; i++) {
            textureProperties.addSkin(skins[i]);
        }
        if (!textureProperties.isDisplayed) {
            return;
        }

        const name = this.tiledObj.name;

        const collides = this.isCustomPropertyByName('collides');

        const interactionInformation = this.getInteractionInformations();

        switch (this.tiledObj.type) {
        case 'decoration':
            return new DecorationObject(
                scene,
                name,
                textureProperties,
                cart_coords,
                this.getIsometricGeom(),
                collides
            );
        case 'door':
            if (equipmentEntity instanceof Door) {
                return new DoorObject(
                    scene,
                    name,
                    textureProperties,
                    cart_coords,
                    this.getIsometricGeom(),
                    equipmentEntity,
                    group
                );
            } else {throw new Error("Couldn't find the corresponding door for this tiled object: " + this.tiledObj.name);}
        case 'door_ground':
            if (equipmentEntity instanceof Door) {
                return new DoorGroundObject(
                    scene,
                    name,
                    textureProperties,
                    cart_coords,
                    this.getIsometricGeom(),
                    equipmentEntity,
                    group
                );
            } else {throw new Error("Couldn't find the corresponding door for this tiled object: " + this.tiledObj.name);}
        case 'interact':
            return new InteractObject(
                scene,
                name,
                textureProperties,
                cart_coords,
                this.getIsometricGeom(),
                collides,
                group,
                interactionInformation
            );
        case 'equipment':
            if (equipmentEntity instanceof Equipment) {
                return new EquipmentObject(
                    scene,
                    name,
                    textureProperties,
                    cart_coords,
                    this.getIsometricGeom(),
                    equipmentEntity,
                    collides,
                    group,
                    interactionInformation
                );
            } else {break;}
        case 'shelf':
            return new ShelfObject(
                scene,
                name,
                textureProperties,
                cart_coords,
                this.getIsometricGeom(),
                collides,
                group
            );
        case 'patrol_ship':
            if (equipmentEntity instanceof Equipment) {
                return new PatrolShipObject(
                    scene,
                    name,
                    textureProperties,
                    cart_coords,
                    this.getIsometricGeom(),
                    equipmentEntity,
                    collides,
                    group,
                    interactionInformation
                );
            } else {break;}
        }

        throw new Error(this.tiledObj.type + " type does not exist for this tiled object: " + this.tiledObj.name);
    }

    //Isometric size of the object is stored as a custom property of the object
    getObjectIsoSize(): IsometricCoordinates
    {
        return new IsometricCoordinates(
            this.getCustomPropertyNumberByName('isoSizeX'),
            this.getCustomPropertyNumberByName('isoSizeY')
        );
    }

    isCustomPropertyByName(property: string): boolean
    {
        const existingKeys = ['grouped', 'collides', 'isOnFront', 'skin'];
        if (existingKeys.includes(property)) {
            for (let i = 0; i < this.tiledObj.properties.length; i++) {
                if (this.tiledObj.properties[i].name === property) {
                    return this.tiledObj.properties[i].value;
                }
            }
        }
        return false;
    }

    getCustomPropertyNumberByName(property: string): number
    {
        const existingKeys = ['depth', 'isoSizeX', 'isoSizeY', 'isoShiftHitboxX', 'isoShiftHitboxY'];
        if (existingKeys.includes(property)) {
            for (let i = 0; i < this.tiledObj.properties.length; i++) {
                if (this.tiledObj.properties[i].name === property) {
                    return this.tiledObj.properties[i].value;
                }
            }
        }
        return 0;
    }

    getCustomPropertyByName(property: string): string
    {
        const existingKeys = ['gameEquipment'];
        if (existingKeys.includes(property)) {
            for (let i = 0; i < this.tiledObj.properties.length; i++) {
                if (this.tiledObj.properties[i].name === property) {
                    return this.tiledObj.properties[i].value;
                }
            }
        }
        return '';
    }

    getInteractionInformations(): InteractionInformation | null
    {
        const intercationInformation = {
            sitAnimation: 'none',
            sitDepth: 1,
            sitFlip: false,
            sitAutoTrigger: false,
            sitX: 0,
            sitY: 0
        };

        const isPropertyFound = {
            sitAnimation: false,
            sitX: false,
            sitY: false
        };

        for (let i = 0; i < this.tiledObj.properties.length; i++) {
            switch (this.tiledObj.properties[i].name) {
            case 'sitX':
                intercationInformation.sitX = this.tiledObj.properties[i].value;
                isPropertyFound.sitX = true;
                break;
            case 'sitY':
                intercationInformation.sitY = this.tiledObj.properties[i].value;
                isPropertyFound.sitY = true;
                break;
            case 'sitAnimation':
                intercationInformation.sitAnimation = this.tiledObj.properties[i].value;
                isPropertyFound.sitAnimation = true;
                break;
            case 'sitDepth':
                intercationInformation.sitDepth = this.tiledObj.properties[i].value;
                break;
            case 'sitFlip':
                intercationInformation.sitFlip = this.tiledObj.properties[i].value;
                break;
            case 'sitAutoTrigger':
                intercationInformation.sitAutoTrigger = this.tiledObj.properties[i].value;
                break;
            }
        }

        if (
            isPropertyFound.sitX && isPropertyFound.sitY &&
            isPropertyFound.sitAnimation
        ) {
            return intercationInformation;
        }

        return null;
    }

    //tiled object coordinates in the isometric frame
    //This function computes cartesian coordinates for the object
    getObjectCartesianCoordinates(): CartesianCoordinates
    {
        if (this.tiledObj.x === undefined || this.tiledObj.y === undefined  || this.tiledObj.width === undefined || this.tiledObj.height === undefined){
            throw new Error('coordinates should be provided');
        }

        const isoCoords = new IsometricCoordinates(this.tiledObj.x, this.tiledObj.y);
        const cartCoords = isoCoords.toCartesianCoordinates();

        //The tiled coordinates should be given relative to the bottom left of the object
        //hence this shift of half the size of the sprite
        return new CartesianCoordinates(cartCoords.x, cartCoords.y);
    }


    //bounding box of the object is not really fit to the object (due to isometric projection)
    //let compute the coordinates of the bottom left of the object in isometric coordinates
    //to do this we use the isoSizeX and isoSizeY custom properties of the object
    getIsometricGeom(): IsometricGeom
    {
        if (this.tiledObj.x === undefined || this.tiledObj.y === undefined || this.tiledObj.height === undefined){
            throw new Error('coordinates should be provided');
        }

        const isoSize = this.getObjectIsoSize();
        const isoShift = new IsometricCoordinates(
            this.getCustomPropertyNumberByName('isoShiftHitboxX'),
            this.getCustomPropertyNumberByName('isoShiftHitboxY')
        );

        const CartCoords = (new IsometricCoordinates(this.tiledObj.x + isoShift.x, this.tiledObj.y + isoShift.y)).toCartesianCoordinates();

        //The center of the isometric shape is different from the center of the sprite (i.e. we need to remove the height part of the object
        const cartGroundCenter = new CartesianCoordinates(CartCoords.x, CartCoords.y + this.tiledObj.height/2 - (isoSize.x + isoSize.y)/4);

        const isoCoords = cartGroundCenter.toIsometricCoordinates();

        return new IsometricGeom(isoCoords, isoSize);
    }


    getGroupName(): string
    {
        switch (this.tiledObj.type) {
        case 'door':
        case 'door_ground':
        case 'equipment':
        case 'patrol_ship':
            return this.getCustomPropertyByName('gameEquipment');
        case 'shelf':
            return 'shelf';
        }

        return 'undefined';
    }


    // This function extract the tileset corresponding to a given gid
    // (is gid comprised between first gid of this tileset and the first gid of next tileset)
    getTileset(tilesets: Phaser.Tilemaps.Tileset[]): Phaser.Tilemaps.Tileset
    {
        let chosenTileset = tilesets[0];
        const gid = this.tiledObj.gid;

        if (gid === undefined) {
            throw new Error('object gid is undefined');
        }

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
}
