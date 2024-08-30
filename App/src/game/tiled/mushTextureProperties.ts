import Phaser from "phaser";
import DecorationObject from "@/game/objects/decorationObject";
import { Door } from "@/entities/Door";
import DoorObject from "@/game/objects/doorObject";
import DoorGroundObject from "@/game/objects/doorGroundObject";
import InteractObject from "@/game/objects/interactObject";
import { Equipment } from "@/entities/Equipment";
import EquipmentObject from "@/game/objects/equipmentObject";
import ShelfObject from "@/game/objects/shelfObject";

export default class mushTextureProperties {
    public textureName: string;
    public frameKey: string;
    public isAnimated: boolean;
    public frames: number[];
    public frameRate: number;
    public replayDelay: number;
    public skins: string[];

    constructor() {
        this.textureName = '';
        this.frameKey = '';
        this.isAnimated = false;

        this.frameRate = 0;
        this.frames = [];
        this.skins = [];
    }

    setTexturesProperties(
        tiledObj: Phaser.Types.Tilemaps.TiledObject,
        tileset: Phaser.Tilemaps.Tileset
    )
    {
        if (tiledObj.gid === undefined){
            throw new Error(tiledObj.name + "gid is not defined");
        }
        const frameNumber = tiledObj.gid - tileset.firstgid;
        const tiledData = tileset.tileData[frameNumber];

        this.setTextureName(tiledObj);

        if (tiledData === undefined) {
            this.frameKey = tiledObj.name;
            this.isAnimated = false;
        } else {
            this.frameKey = tiledObj.name + '-0';
            this.isAnimated = true;

            const animation = tiledData.animation;
            const animationLength = animation.length;

            const frameDuration = animation[0].duration;
            this.frameRate = 1000/frameDuration;

            for (let i = 0; i < animationLength; i++) {
                const currentFrame = animation[i];
                this.frames[i] = currentFrame.tileid;
            }
            this.replayDelay = animation[animationLength-1].duration - frameDuration;
        }
    }

    setTextureName(tiledObj: Phaser.Types.Tilemaps.TiledObject)
    {
        switch (tiledObj.type) {
        case 'shelf':
        case 'door':
        case 'door_ground':
            this.textureName = 'base_textures';
            break;
        case 'decoration':
        case 'interact':
        case 'equipment':
        case 'patrol_ship':
            this.textureName = 'equipments';
            break;
        default:
            throw new Error(this.tiledObj.type + " type does not exist for this tiled object: " + this.tiledObj.name);
        }
    }

    setCharacterTexture(characterKey: string)
    {
        this.textureName = 'characters';
        this.frameKey = characterKey+'-0';
    }
}
