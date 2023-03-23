import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { IsometricCoordinates, CartesianCoordinates } from "@/game/types";
import IsometricGeom from "@/game/scenes/isometricGeom";


/*eslint no-unused-vars: "off"*/
export default class DecorationObject extends Phaser.GameObjects.Sprite {
    protected animName : string|null = null;
    protected tiledFrame: number;
    public isoGeom: IsometricGeom;
    public isoHeight: number;
    public collides: boolean;
    protected group: Phaser.GameObjects.Group | null;

    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        tileset: Phaser.Tilemaps.Tileset,
        frame: number,
        name: string,
        isFlipped: { x: boolean, y: boolean},
        collides: boolean,
        isAnimationYoyo: boolean,
        group: Phaser.GameObjects.Group | null = null
    )
    {
        super(scene, cart_coords.x, cart_coords.y, name);

        this.scene = scene;
        this.name = name;
        this.isoGeom = iso_geom;
        this.tiledFrame = frame;
        this.group = group;
        this.collides = collides;

        this.scene.add.existing(this);

        if (group !== null) {
            group.add(this);
        }

        this.applyTexture(tileset, name, isFlipped, isAnimationYoyo);

        this.isoHeight = this.height - (this.isoGeom.getIsoSize().x + this.isoGeom.getIsoSize().y)/2;


        /* const graphics = this.scene.add.graphics();
        graphics.lineStyle(1, 0x000000, 0.5);
        graphics.fillStyle(0xff0000, 1);
        graphics.fillPoints(this.isoGeom.getCartesianPolygon().points, true);*/
    }

    applyTexture(
        tileset: Phaser.Tilemaps.Tileset,
        name: string,
        isFlipped: { x: boolean, y: boolean },
        isAnimationYoyo: boolean
    ): void
    {
        //@ts-ignore
        const tiledData = tileset.tileData[this.tiledFrame];

        if (tiledData)
        {
            this.animName = `${name}Animation`;

            const animationLength = tiledData.animation.length;
            const endFrame = this.tiledFrame + animationLength - 1;
            const frames = this.anims.generateFrameNames(tileset.name, { start: this.tiledFrame, end: endFrame });
            const duration = tiledData.animation[0].duration;

            this.anims.create({
                key: this.animName,
                frames: frames,
                duration: duration * animationLength,
                repeat: -1
            });

            this.anims.play({ key: this.animName, yoyo: isAnimationYoyo });

        } else {
            this.setTexture(tileset.name, this.tiledFrame);
        }

        this.flipX = isFlipped.x;
        this.flipY = isFlipped.y;
    }

    //@ts-ignore
    getRandomPoint(point: Phaser.Geom.Point): Phaser.Geom.Point
    {
        const randomHeight = Phaser.Math.Between(0, this.isoHeight);
        const randomGround = this.isoGeom.getRandomPoint(point);

        point.setTo(randomGround.x, randomGround.y - randomHeight);
        return point;
    }

    setPositionFromIsometricCoordinates( isoCoords: IsometricCoordinates )
    {
        const cartCoords = isoCoords.toCartesianCoordinates();

        this.x = cartCoords.x;
        this.y = cartCoords.y;
    }

    delete()
    {
        this.destroy();
    }
}
