import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates } from "@/game/types";
import { Door as DoorEntity } from "@/entities/Door";
import store from "@/store";
import { Action } from "@/entities/Action";
import InteractObject from "@/game/objects/interactObject";
import IsometricGeom from "@/game/scenes/isometricGeom";


export default class DoorGroundObject extends InteractObject {
    public door: DoorEntity;
    protected particles: Phaser.GameObjects.Particles.ParticleEmitter | null = null;

    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        tileset: Phaser.Tilemaps.Tileset,
        firstFrame: number,
        isFlipped: { x: boolean, y: boolean},
        door: DoorEntity,
        group: Phaser.GameObjects.Group | null = null,
    )
    {
        super(scene, cart_coords, iso_geom, tileset, firstFrame, door.key, isFlipped, true, false, group);

        this.door = door;

        this.handleBroken();
        this.updateDoor(door);

        this.on('pointerdown', () => { this.onDoorClicked(); }, this);
    }

    updateDoor(door: DoorEntity | null = null) {
        if (door !== null) {
            this.door = door;
        }

        this.handleBroken();
    }

    handleBroken(): void
    {
        if (this.door.isBroken &&
            this.particles === null &&
            (this.tiledFrame === 0 || this.tiledFrame === 10)
        ) {
            this.particles = this.scene.add.particles(0,0, 'smoke_particle', {
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
        for (let i = 0; i < this.door.actions.length; i++) {
            const actionObject = this.door.actions[i];
            if (actionObject.key === 'move') {
                return actionObject;
            }
        }

        throw new Error('door do not have the move action');
    }

    onDoorClicked(): void
    {
        // if player click on the door AND the door is closed
        if(
            !this.isOpen()  &&
            !this.door.isBroken
        )
        {
            this.activateDoor();
            this.activateOtherPartOfDoor();
            store.dispatch('room/selectTarget', { target: null });

            // if player click on the door AND the door is open AND player can move
        } else if (!this.door.isBroken && this.getMoveAction().canExecute) {
            const moveAction = this.getMoveAction();
            store.dispatch('action/executeAction', { target: this.door, action: moveAction });
            store.dispatch('room/selectTarget', { target: null });
            store.dispatch('room/closeInventory');

            // If the door is broken propose the repair action
        } else {
            const door = this.door;
            store.dispatch('room/selectTarget', { target: this.door });
        }
    }

    onClickedOut() {
        super.onClickedOut();

        this.activateDoor();
        // also activate doors in the same group
        this.activateOtherPartOfDoor();
    }

    activateOtherPartOfDoor(): void
    {
        if (this.group !== null) {
            this.group.getChildren().forEach((object: Phaser.GameObjects.GameObject) => {
                if (object instanceof DoorGroundObject && object !== this) {
                    object.activateDoor();
                }
            });
        }
    }

    isOpen(): boolean
    {
        return this.anims.isPlaying;
    }

    activateDoor(): void
    {
        if (!this.isOpen()) {
            if (this.animName !== null) {
                this.anims.play(this.animName);
            }
        } else {
            this.anims.stopAfterRepeat();
        }
    }

    applyTexture(
        tileset: Phaser.Tilemaps.Tileset,
        name: string,
        isFlipped: { x: boolean, y: boolean },
        isAnimationYoyo: boolean
    ) {
        super.applyTexture(tileset, name, isFlipped, isAnimationYoyo);

        this.anims.stop();
    }

    setHoveringOutline(): void
    {
        if (this.door.isBroken || (!this.canMove())) {
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
        if (this.door.isBroken || (!this.canMove())) {
            this.setPostPipeline('outline');
            const pipeline = this.postPipelines[0];
            //@ts-ignore
            pipeline.resetFromJSON({ thickness: 1, outlineColor: 0xff0000 });
        } else {
            super.setSelectedOutline();
        }
    }

    canMove(): boolean
    {
        const moveAction = this.door.actions.filter((action: Action) => {return action.key === 'move';});

        if (moveAction.length !==1 ) {
            return false;
        }

        return moveAction[0].canExecute;
    }

    delete()
    {
        this.particles?.destroy();
        super.delete();
    }
}
