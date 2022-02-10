import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { IsometricCoordinates, CartesianCoordinates } from "@/game/types";
import DecorationObject from "@/game/objects/decorationObject";
import IsometricGeom from "@/game/objects/isometricGeom";

/*eslint no-unused-vars: "off"*/
export default class InteractObject extends DecorationObject {
    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        tileset: Phaser.Tilemaps.Tileset,
        frame: number,
        name: string,
        sceneAspectRatio: IsometricCoordinates
    )
    {
        super(scene, cart_coords, iso_geom, tileset, frame, name, sceneAspectRatio);

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

    getRandomPoint(point: Phaser.Geom.Point): Phaser.Geom.Point {

        const x = Phaser.Math.Between(0, this.width - 1);
        const y = Phaser.Math.Between(0, this.height - 1);

        return point.setTo(x + this.x, - y + this.y);
    }
}
