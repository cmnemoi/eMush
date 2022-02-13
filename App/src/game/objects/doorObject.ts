import * as Phaser from "phaser";
import Vector2 = Phaser.Math.Vector2;
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import { Door as DoorEntity } from "@/entities/Door";
import { Action } from "@/entities/Action";
import store from "@/store";
import InteractObject from "@/game/objects/interactObject";
import IsometricGeom from "@/game/objects/isometricGeom";


export default class DoorObject extends InteractObject {
    private openFrames: Phaser.Types.Animations.AnimationFrame[];
    private closeFrames: Phaser.Types.Animations.AnimationFrame[];
    private door : DoorEntity;

    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        tileset: Phaser.Tilemaps.Tileset,
        tiledFrame: number,
        door: DoorEntity,
        sceneAspectRatio: IsometricCoordinates
    )
    {
        super(scene, cart_coords, iso_geom, tileset, tiledFrame, door.key, sceneAspectRatio);

        this.door = door;

        this.openFrames = this.anims.generateFrameNames('door_object', { start: this.tiledFrame, end: this.tiledFrame + 10 });

        this.closeFrames = this.anims.generateFrameNames('door_object', { start: this.tiledFrame + 10, end: this.tiledFrame + 23 });
        this.closeFrames[this.closeFrames.length + 1] = this.openFrames[0];

        // doors are always on the bottom (just in front of the back_wall layer)
        this.setDepth(0);

        //this.scene.input.enableDebug(this, 0xff00ff);

        this.createAnimations();

        this.scene.input.on('pointerdown', (pointer: Phaser.Input.Pointer) => {
            this.onDoorClicked(pointer);
            this.scene.input.stopPropagation();
        }, this);
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

    applyTexture(tileset: Phaser.Tilemaps.Tileset, name: string, isAnimationYoyo?: boolean) {
        this.setTexture('door_object', this.tiledFrame);
    }

    createInteractionArea():void
    {
        this.setInteractive(this.setInteractBox(), Phaser.Geom.Polygon.Contains);
    }

    onDoorClicked(pointer: Phaser.Input.Pointer): void
    {
        const objectX = pointer.worldX - (this.x - this.width/2);
        const objectY = pointer.worldY - (this.y - this.height/2);

        if (this.input.hitArea.contains(objectX, objectY)){
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
}
