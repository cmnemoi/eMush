import * as Phaser from "phaser";
import Vector2 = Phaser.Math.Vector2;
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates } from "@/game/types";
import { Door as DoorEntity } from "@/entities/Door";
import IsometricGeom from "@/game/scenes/isometricGeom";
import DoorGroundObject from "@/game/objects/doorGroundObject";
import mushTextureProperties from "@/game/tiled/mushTextureProperties";


export default class DoorObject extends DoorGroundObject {
    public openedFrameId: number;
    protected closedFrameId: number;

    constructor(
        scene: DaedalusScene,
        name: string,
        textureProperties: mushTextureProperties,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        door: DoorEntity,
        group: Phaser.GameObjects.Group | null = null
    )
    {
        super(scene, name, textureProperties, cart_coords, iso_geom, door, group);
    }


    applyTexture(
        textureProperties: mushTextureProperties
    ) {
        this.closedFrameId = textureProperties.frames[0];
        this.openedFrameId = 10 + this.closedFrameId;

        this.setTexture(textureProperties.textureName, this.name+'-' + this.closedFrameId);
        // create animations
        const openFrames = this.anims.generateFrameNames(textureProperties.textureName, {
            prefix: this.name+'-',
            start: this.closedFrameId,
            end: this.openedFrameId
        });

        const closeFrames = this.anims.generateFrameNames(textureProperties.textureName, {
            prefix: this.name+'-',
            start: this.openedFrameId,
            end: 23 + this.closedFrameId
        });
        closeFrames[closeFrames.length + 1] = openFrames[0];

        this.anims.create({
            key: 'door_open',
            frames: openFrames,
            frameRate: 10,
            repeat: 0
        });

        this.anims.create({
            key: 'door_close',
            frames: closeFrames,
            frameRate: 10,
            repeat: 0
        });
    }

    createInteractionArea():void
    {
        this.setInteractive(this.setInteractBox(), Phaser.Geom.Polygon.Contains);
    }

    isOpen(): boolean
    {
        if (this.anims.currentFrame instanceof  Phaser.Animations.AnimationFrame) {
            return this.anims.currentFrame.index ===  11 && this.anims.getName() ===  'door_open';
        }
        return false;
    }

    activateDoor(): void
    {
        if (!this.anims.isPlaying) {
            if (!this.isOpen()) {
                this.anims.play('door_open');
            } else {
                this.anims.play('door_close');
            }
        } else {
            this.anims.reverse();
        }
    }

    handleBroken(): void
    {
        if (this.door.isBroken && this.particles === null) {
            this.particles = this.scene.add.particles(0, 0, 'base_textures', {
                frame: 'smoke_particle',
                x: 0,
                y: 0,
                lifespan: { min: 1000, max: 1200 },
                speed: { min: 10, max: 30 },
                angle: { min: 260, max: 280 },
                gravityY: 10,
                scale: { start: 0.5, end: 2, ease: 'Quad.easeIn' },
                alpha: { start: 0.5, end: 0, ease: 'Quad.easeIn' },
                tint: [ 0x666666, 0xFFFFFF, 0x10EEEEEE ],
                quantity: 1,
                frequency: 100000/(this.width * this.height ),
                //@ts-ignore
                emitZone: { type: 'random', source: this.isoGeom }
            });
        } else if (this.particles !== null  && !this.door.isBroken) {
            this.particles.destroy();
            this.particles = null;
        }

        if (this.particles !== null) {
            this.particles.setDepth(this.depth + 1);
        }
    }

    setInteractBox() : Phaser.Geom.Polygon
    {
        const leftDoorsFrames = [0, 48, 96, 144];

        if (leftDoorsFrames.includes(this.tiledFrame))
        {
            return new Phaser.Geom.Polygon([
                new Vector2(4, 35),
                new Vector2(34, 20),
                new Vector2( 34,  58),
                new Vector2(4,  73)
            ]);
        } else {
            return new Phaser.Geom.Polygon([
                new Vector2(14, 20),
                new Vector2(44,  35),
                new Vector2( 44,  73),
                new Vector2(14,   58)
            ]);
        }
    }

    setHoveringOutline(): void
    {
        if (this.door.isBroken || (!this.getMoveAction().canExecute)) {
            this.setPostPipeline('outline');
            const pipeline = this.postPipelines[0];
            //@ts-ignore
            pipeline.resetFromJSON({ thickness: 1, outlineColor: 0xff0000 });
        } else {
            super.setHoveringOutline();
        }
    }

    setSelectedOutline(): void
    {
        if (this.door.isBroken || (!this.getMoveAction().canExecute)) {
            this.setPostPipeline('outline');
            const pipeline = this.postPipelines[0];
            //@ts-ignore
            pipeline.resetFromJSON({ thickness: 1, outlineColor: 0xff0000 });
        } else {
            super.setSelectedOutline();
        }
    }
}
