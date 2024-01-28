import * as Phaser from "phaser";
import Vector2 = Phaser.Math.Vector2;
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates } from "@/game/types";
import { Door as DoorEntity } from "@/entities/Door";
import IsometricGeom from "@/game/scenes/isometricGeom";
import DoorGroundObject from "@/game/objects/doorGroundObject";


export default class DoorObject extends DoorGroundObject {
    private openFrames: Phaser.Types.Animations.AnimationFrame[];
    private closeFrames: Phaser.Types.Animations.AnimationFrame[];

    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        tileset: Phaser.Tilemaps.Tileset,
        tiledFrame: number,
        isFlipped: { x: boolean, y: boolean},
        door: DoorEntity,
        group: Phaser.GameObjects.Group | null = null,
    )
    {
        super(scene, cart_coords, iso_geom, tileset, tiledFrame, isFlipped, door, group);

        // create animations
        this.openFrames = this.anims.generateFrameNames('door_object', { start: this.tiledFrame, end: this.tiledFrame + 10 });
        this.closeFrames = this.anims.generateFrameNames('door_object', { start: this.tiledFrame + 10, end: this.tiledFrame + 23 });
        this.closeFrames[this.closeFrames.length + 1] = this.openFrames[0];
        this.createAnimations();
    }

    createAnimations(): void
    {
        this.anims.create({
            key: 'door_open',
            frames: this.openFrames,
            frameRate: 10,
            repeat: 0
        });

        this.anims.create({
            key: 'door_close',
            frames: this.closeFrames,
            frameRate: 10,
            repeat: 0
        });
    }

    applyTexture(
        tileset: Phaser.Tilemaps.Tileset,
        name: string,
        isFlipped: { x: boolean, y: boolean },
        isAnimationYoyo: boolean
    ) {
        this.setTexture('door_object', this.tiledFrame);
        this.flipX = isFlipped.x;
        this.flipY = isFlipped.y;
    }

    createInteractionArea():void
    {
        this.setInteractive(this.setInteractBox(), Phaser.Geom.Polygon.Contains);
    }

    isOpen(): boolean
    {
        return String(this.frame.name) !==  String(this.tiledFrame);
    }

    activateDoor(): void
    {
        if (!this.isOpen()) {
            this.anims.play('door_open');
        } else {
            const currentFrame = this.anims.currentFrame;
            let startFrame = 0;
            if (currentFrame!== null) {
                startFrame = 11 - currentFrame.index;
            }
            this.anims.play({ key: 'door_close', startFrame: startFrame });
        }
    }

    handleBroken(): void
    {
        if (this.door.isBroken && this.particles === null) {
            this.particles = this.scene.add.particles(0, 0, 'smoke_particle', {
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
