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
import { skinEnum, FrameTransformation, SkinInfo, SkinEnum } from "../../../public/phaser/skin.enum";
import { characterEnum } from "@/enums/character";

export default class mushTextureProperties {
    public textureName: string;
    public frameKey: string;
    public firstFrameKey: string;
    public isAnimated: boolean;
    public frames: number[];
    public frameRate: number;
    public replayDelay: number;
    public isDisplayed: boolean;

    constructor() {
        this.textureName = '';
        this.frameKey = '';
        this.firstFrameKey = '';
        this.isAnimated = false;

        this.frameRate = 0;
        this.replayDelay = 0;
        this.frames = [];
        this.isDisplayed = true;
    }

    setTexturesProperties(
        tiledObj: Phaser.Types.Tilemaps.TiledObject,
        tileset: Phaser.Tilemaps.Tileset,
        isSkin: boolean
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
            this.frameKey = tiledObj.name + '-' + frameNumber.toString();
            this.isAnimated = false;
        } else {
            this.frameKey = tiledObj.name;
            this.firstFrameKey = tiledObj.name + '-0';
            this.isAnimated = true;

            const animation = tiledData.animation;
            this.generateFramesFromAnimation(animation);
        }
        if (isSkin) {
            this.isDisplayed = false;
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
        if (skinInfo === undefined) {
            return;
        }

        const newFrame = this.getSkinTransformation(skinInfo);
        if (newFrame === undefined) {
            return;
        }

        const type = skinInfo.type;
        switch (type) {
        case SkinEnum.TYPE_REPLACE:
            this.replaceTexture(skinInfo);
            break;
        case SkinEnum.TYPE_HIDE:
            this.isDisplayed = false;
            break;
        case SkinEnum.TYPE_SHOW:
            this.isDisplayed = true;
        }
    }

    getSkinTransformation(skinInfo: SkinInfo): FrameTransformation
    {
        return skinInfo.frameChanges[this.frameKey];
    }

    replaceTexture(skinInfo: SkinInfo): string
    {
        const newFrame = this.getSkinTransformation(skinInfo);
        if (newFrame !== undefined) {
            this.frameKey = newFrame.newFrame;
            this.firstFrameKey = newFrame.newFrame;
        }

        const newAnimation = skinInfo.animationChange;
        if (!newAnimation) {
            return this.frameKey;
        } else {
            this.generateFramesFromAnimation(newAnimation);
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
