import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import IsometricGeom from "@/game/scenes/isometricGeom";
import mushTextureProperties from "@/game/tiled/mushTextureProperties";


export default class DecorationObject extends Phaser.GameObjects.Sprite {
    protected name: string;
    protected tiledFrame: number;
    public isoGeom: IsometricGeom;
    public isoHeight: number;
    public collides: boolean;
    protected group: Phaser.GameObjects.Group | null;

    constructor(
        scene: DaedalusScene,
        name: string,
        textureProperties: mushTextureProperties,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        collides: boolean,
        group: Phaser.GameObjects.Group | null = null
    )
    {
        super(scene, cart_coords.x, cart_coords.y, textureProperties.textureName);

        this.scene = scene;
        this.isoGeom = iso_geom;
        this.group = group;
        this.collides = collides;
        this.name = name;

        this.scene.add.existing(this);

        if (group !== null) {
            group.add(this);
        }

        this.applyTexture(textureProperties);

        this.isoHeight = this.height - (this.isoGeom.getIsoSize().x + this.isoGeom.getIsoSize().y)/2;

        /* const graphics = this.scene.add.graphics();
        graphics.lineStyle(1, 0x000000, 0.5);
        graphics.fillStyle(0xff0000, 1);
        graphics.fillPoints(this.isoGeom.getCartesianPolygon().points, true);*/
    }

    applyTexture(
        textureProperties: mushTextureProperties
    ): void
    {
        if (textureProperties.isAnimated) {
            const textureName = textureProperties.textureName;
            const prefix = textureProperties.frameKey;

            const frames = this.anims.generateFrameNames(textureName, { prefix: prefix+'-', frames: textureProperties.frames  });

            const anim = this.anims.create({
                key: this.name + '_animation',
                frames: frames,
                frameRate: textureProperties.frameRate,
                repeat: -1,
                repeatDelay: textureProperties.replayDelay
            });

            this.anims.play(anim);
        } else {
            this.setFrame(textureProperties.frameKey);
        }
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
