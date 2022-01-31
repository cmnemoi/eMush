import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { IsometricCoordinates, CartesianCoordinates, IsometricDistance } from "@/game/types";


/*eslint no-unused-vars: "off"*/
export default class DecorationObject extends Phaser.GameObjects.Sprite {
    protected animName : string|null = null;
    public sceneAspectRatio: IsometricCoordinates;


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
        super(scene, cart_coords.x, cart_coords.y, name);

        this.scene = scene;
        this.name = name;
        this.sceneAspectRatio = sceneAspectRatio;

        //the first sprite to be displayed are the ones on the last row of either x or y isometric coordinates
        //a second order sorting is applied using the y axis of cartesian coordinates
        //              4
        //            3   3
        //          2   3    2
        //       1    2   2    1           / \
        //          1   2   1             y   x
        //              1
        //
        this.setDepth(Math.max(iso_coords.x + sceneAspectRatio.x, iso_coords.y + sceneAspectRatio.y)*1000 + this.y + this.width/2);


        this.scene.add.existing(this);

        this.applyTexture(tileset, frame, name);
    }

    applyTexture(tileset: Phaser.Tilemaps.Tileset , frame: number, name: string): void
    {
        //@ts-ignore
        if (tileset.tileData[frame])
        {
            this.animName = `${name}Animation`;

            //@ts-ignore
            const endFrame = frame + tileset.tileData[frame].animation.length -1;
            const frames = this.anims.generateFrameNames(tileset.name, { start: frame, end: endFrame });


            this.anims.create({
                key: this.animName,
                frames: frames,
                frameRate: 10,
                repeat: -1
            });

            this.anims.play(this.animName);
        } else {
            this.setTexture(tileset.name, frame);
        }
    }
}
