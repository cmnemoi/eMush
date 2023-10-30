import * as Phaser from "phaser";
import Vector2 = Phaser.Math.Vector2;
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates } from "@/game/types";
import { Door as DoorEntity } from "@/entities/Door";
import { Action } from "@/entities/Action";
import store from "@/store";
import InteractObject from "@/game/objects/interactObject";
import IsometricGeom from "@/game/scenes/isometricGeom";


export default class DoorObject extends InteractObject {
    private openFrames: Phaser.Types.Animations.AnimationFrame[];
    private closeFrames: Phaser.Types.Animations.AnimationFrame[];
    public door : DoorEntity;
    private particles: Phaser.GameObjects.Particles.ParticleEmitter | null = null;

    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        tileset: Phaser.Tilemaps.Tileset,
        tiledFrame: number,
        isFlipped: { x: boolean, y: boolean},
        door: DoorEntity
    )
    {
        super(scene, cart_coords, iso_geom, tileset, tiledFrame, door.key, isFlipped, false, false);

        this.door = door;
        this.handleBroken();

        this.openFrames = this.anims.generateFrameNames('door_object', { start: this.tiledFrame, end: this.tiledFrame + 10 });

        this.closeFrames = this.anims.generateFrameNames('door_object', { start: this.tiledFrame + 10, end: this.tiledFrame + 23 });
        this.closeFrames[this.closeFrames.length + 1] = this.openFrames[0];

        // doors are always on the bottom (just in front of the back_wall layer)
        this.setDepth(0);

        this.createAnimations();
    }

    updateDoor(door: DoorEntity | null = null) {
        if (door !== null) {
            this.door = door;
        }
        this.handleBroken();
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

    onDoorClicked(pointer: Phaser.Input.Pointer): void
    {
        const objectX = pointer.worldX - (this.x - this.width/2);
        const objectY = pointer.worldY - (this.y - this.height/2);

        if (this.input && this.input.hitArea.contains(objectX, objectY)){
            if(
                String(this.frame.name) === String(this.tiledFrame)  &&
                !this.door.isBroken
            )
            {
                //if player click on the door AND the door is closed
                this.anims.play('door_open');
                store.dispatch('room/selectTarget', { target: null });
            } else if (!this.door.isBroken && this.getMoveAction().canExecute) {
                //if player click on the door AND the door is open AND player can move
                const moveAction = this.getMoveAction();
                store.dispatch('action/executeAction', { target: this.door, action: moveAction });
                store.dispatch('room/selectTarget', { target: null });
                store.dispatch('room/closeInventory');
            } else {
                //If the door is broken propose the repair action
                const door = this.door;
                store.dispatch('room/selectTarget', { target: this.door });
            }
        } else if (String(this.frame.name) ===  String(this.tiledFrame + 10))
        {
            //if player click outside the door AND the door is open
            this.anims.play('door_close');
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

    getMoveAction(): Action
    {
        const moveAction = this.door.actions.filter((action: Action) => {return action.key === 'move';});

        if (moveAction.length !==1 ) {
            throw new Error("this door should have exactly one move action");
        }

        return moveAction[0];
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

    delete()
    {
        this.particles?.destroy();
        super.delete();
    }
}
