import * as Phaser from "phaser";
import Vector2 = Phaser.Math.Vector2;
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates } from "@/game/types";
import { Door as DoorEntity } from "@/entities/Door";
import { Action } from "@/entities/Action";
import store from "@/store";


export default class DoorObject extends Phaser.GameObjects.Sprite {
    private firstFrame : number;
    private openFrames: Phaser.Types.Animations.AnimationFrame[];
    private closeFrames: Phaser.Types.Animations.AnimationFrame[];
    private door : DoorEntity;

    constructor(scene: DaedalusScene, cart_coords: CartesianCoordinates, firstFrame: number, door: DoorEntity)
    {
        super(scene, cart_coords.x, cart_coords.y, door.name);

        this.scene = scene;
        this.door = door;
        this.firstFrame = firstFrame;

        this.scene.add.existing(this);

        this.openFrames = this.anims.generateFrameNames('door_object', { start: this.firstFrame, end: this.firstFrame + 10 });
        this.closeFrames = this.anims.generateFrameNames('door_object', { start: this.firstFrame + 10, end: this.firstFrame + 23 });

        this.closeFrames[this.closeFrames.length + 1] = this.openFrames[0];

        this.setTexture('door_object', this.firstFrame);

        // doors are always on the bottom (just in front of the back_wall layer)
        this.setDepth(0);

        this.setInteractive(this.setInteractBox(), Phaser.Geom.Polygon.Contains);

        this.scene.input.enableDebug(this, 0xff00ff);

        this.createAnimations();

        this.scene.input.on('pointerdown', (pointer: Phaser.Input.Pointer) => {
            this.onDoorClicked(pointer);
        }, this);

        //  highlight hovered sprite
        this.on('pointerover', () => {
            this.setTint(0xff0000);
        }, this);
        this.on('pointerout', () => {
            this.clearTint();
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

    onDoorClicked(pointer: Phaser.Input.Pointer): void
    {
        const objectX = pointer.worldX - (this.x - this.width/2);
        const objectY = pointer.worldY - (this.y - this.height/2);

        if (this.input.hitArea.contains(objectX, objectY)){
            if(
                String(this.frame.name) === String(this.firstFrame)  &&
                !this.door.isBroken
            )
            {
                //if player click on the door AND the door is closed
                this.anims.play('door_open');
            } else if (!this.door.isBroken) {
                //if player click on the door AND the door is open
                const moveAction = this.getMoveAction();
                store.dispatch('action/executeAction', { target: this.door, action: moveAction });
            } else {
                //If the door is broken propose the repair action
                const door = this.door;
                store.dispatch('room/selectTarget', { target: door });
            }
        } else if (String(this.frame.name) ===  String(this.firstFrame + 10))
        {
            //if player click outside the door AND the door is open
            this.anims.play('door_close');
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

    setInteractBox() : Phaser.Geom.Polygon
    {
        const leftDoorsFrames = [0, 48, 96, 144];

        if (leftDoorsFrames.includes(this.firstFrame))
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
}
