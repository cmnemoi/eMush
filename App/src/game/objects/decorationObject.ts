import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { IsometricCoordinates, CartesianCoordinates } from "@/game/types";
import IsometricGeom from "@/game/objects/isometricGeom";


/*eslint no-unused-vars: "off"*/
export default class DecorationObject extends Phaser.GameObjects.Sprite {
    protected animName : string|null = null;
    protected tiledFrame: number;
    public sceneAspectRatio: IsometricCoordinates;
    public isoGeom: IsometricGeom;
    public isoHeight: number;


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
        super(scene, cart_coords.x, cart_coords.y, name);

        this.scene = scene;
        this.name = name;
        this.sceneAspectRatio = sceneAspectRatio;
        this.isoGeom = iso_geom;
        this.tiledFrame = frame;

        //the first sprite to be displayed are the ones on the last row of either x or y isometric coordinates
        //a second order sorting is applied using the y axis of cartesian coordinates
        //              4
        //            3   3
        //          2   3    2
        //       1    2   2    1           / \
        //          1   2   1             y   x
        //              1
        //
        this.setDepth(Math.max(this.isoGeom.getIsoCoords().x + sceneAspectRatio.x, this.isoGeom.getIsoCoords().y + sceneAspectRatio.y)*1000 + this.y + this.width/2);


        this.scene.add.existing(this);

        this.applyTexture(tileset, name);

        this.isoHeight = this.height - (this.isoGeom.getIsoSize().x + this.isoGeom.getIsoSize().y)/2;
    }

    applyTexture(tileset: Phaser.Tilemaps.Tileset, name: string): void
    {
        //@ts-ignore
        if (tileset.tileData[this.tiledFrame])
        {
            this.animName = `${name}Animation`;

            //@ts-ignore
            const endFrame = this.tiledFrame + tileset.tileData[this.tiledFrame].animation.length -1;
            const frames = this.anims.generateFrameNames(tileset.name, { start: this.tiledFrame, end: endFrame });


            this.anims.create({
                key: this.animName,
                frames: frames,
                frameRate: 10,
                repeat: -1
            });

            this.anims.play(this.animName);
        } else {
            this.setTexture(tileset.name, this.tiledFrame);
        }
        //this.setAlpha(0);
    }

    //@ts-ignore
    getRandomPoint(point: Phaser.Geom.Point): Phaser.Geom.Point
    {
        const randomHeight = Phaser.Math.Between(0, this.isoHeight);
        const randomGround = this.isoGeom.getRandomPoint(point);

        point.setTo(randomGround.x, randomGround.y - randomHeight);
        return point;
    }
}
