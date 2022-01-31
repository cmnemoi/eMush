import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { IsometricCoordinates, CartesianCoordinates, IsometricDistance } from "@/game/types";
import DecorationObject from "@/game/objects/decorationObject";

/*eslint no-unused-vars: "off"*/
export default class InteractObject extends DecorationObject {
    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_coords: IsometricCoordinates,
        tileset: Phaser.Tilemaps.Tileset,
        frame: number,
        name: string,
        sceneAspectRatio: IsometricDistance
    )
    {
        super(scene, cart_coords, iso_coords, tileset, frame, name, sceneAspectRatio);

        this.createInteractionArea();


        this.on('pointerover', () => {
            if (!this.isSelected()) {this.onHovering();}
        }, this);

        this.on('pointerout', () => {
            if (!this.isSelected()) {this.removeOutline();}
        }, this);
    }

    createInteractionArea():void
    {
        this.setInteractive({ pixelPerfect: true });
    }

    onHovering(): void
    {
        this.setPostPipeline('outline');
        const pipeline = this.postPipelines[0];
        //@ts-ignore
        pipeline.resetFromJSON({ thickness: 1, outlineColor: 0xffffff });
    }

    onSelected(): void
    {
        this.setPostPipeline('outline');
        const pipeline = this.postPipelines[0];
        //@ts-ignore
        pipeline.resetFromJSON({ thickness: 1, outlineColor: 0x00CC00 });
    }

    removeOutline(): void
    {
        this.resetPostPipeline();
    }

    isSelected(): boolean
    {
        return (<DaedalusScene>this.scene).selectedGameObject === this;
    }
}
