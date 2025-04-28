import Phaser from "phaser";
import DecorationObject from "@/game/objects/decorationObject";
import { Door } from "@/entities/Door";
import DoorObject from "@/game/objects/doorObject";
import DoorGroundObject from "@/game/objects/doorGroundObject";
import InteractObject from "@/game/objects/interactObject";
import { Equipment } from "@/entities/Equipment";
import EquipmentObject from "@/game/objects/equipmentObject";
import ShelfObject from "@/game/objects/shelfObject";
import { Skin } from "@/entities/Skin";
import { skinEnum } from "../../../public/phaser/skin.enum";
import { characterEnum } from "@/enums/character";

export default class mushTextureProperties {
    public textureName: string;
    public frameKey: string;
    public firstFrameKey: string;
    public isAnimated: boolean;
    public frames: number[];
    public frameRate: number;
    public replayDelay: number;

    constructor() {
        this.textureName = '';
        this.frameKey = '';
        this.firstFrameKey = '';
        this.isAnimated = false;

        this.frameRate = 0;
        this.replayDelay = 0;
        this.frames = [];
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
            this.firstFrameKey = tiledObj.name;
            this.frameKey = tiledObj.name;
            this.isAnimated = false;
        } else {
            this.frameKey = tiledObj.name;
            this.firstFrameKey = tiledObj.name + '-0';
            this.isAnimated = true;

            const animation = tiledData.animation;
            this.generateFramesFromAnimation(animation);
        }
    }


    generateFramesFromAnimation(animation: Array<{ duration: number, tileid: number }>): void
    {
        const animationLength = animation.length;
        const frameDuration = animation[0].duration;
        this.frameRate = 1000/frameDuration;
        for (let i = 0; i < animationLength; i++) {
            const currentFrame = animation[i];
            this.frames[i] = currentFrame.tileid;
        }
        this.replayDelay = animation[animationLength-1].duration - frameDuration;
    }
    addSkin(skin: Skin): void
    {
        const skinName =  skin.skinName;
        if (skinName === null) {
            return;
        }
        const skinInfo = skinEnum[skinName];

        const frameChanges = skinInfo.frameChanges;
        this.replaceTexture(frameChanges);

        const newAnimation = skinInfo.animationChange;
        if (!newAnimation) {
            return;
        } else {
            this.generateFramesFromAnimation(newAnimation);
        }
    }

    replaceTexture(frameChanges: Array<{ initialFrame: string, newFrame: string }>): string
    {
        for (let i = 0; i < frameChanges.length; i++) {
            if (frameChanges[i].initialFrame === this.frameKey) {
                this.frameKey = frameChanges[i].newFrame;
                this.firstFrameKey = frameChanges[i].newFrame;

                return this.frameKey;
            }
        }
        return this.frameKey;
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
