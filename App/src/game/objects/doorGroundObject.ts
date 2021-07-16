import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates } from "@/game/types";
import { Door as DoorEntity } from "@/entities/Door";
import store from "@/store";


/*eslint no-unused-vars: "off"*/
export default class DoorGroundObject extends Phaser.GameObjects.Sprite {
    private frames: Phaser.Types.Animations.AnimationFrame[];
    protected door: DoorEntity;

    constructor(scene: DaedalusScene, cart_coords: CartesianCoordinates, firstFrame: number, door: DoorEntity)
    {
        super(scene, cart_coords.x, cart_coords.y, door.name);

        this.scene = scene;
        this.door = door;

        this.scene.add.existing(this);

        if (firstFrame === 5 || firstFrame === 15){
            this.setDepth(0);
        } else {
            this.setDepth(this.y + this.width/2);
        }

        this.frames = this.anims.generateFrameNames('door_ground_object', { start: firstFrame, end: firstFrame + 3 });

        this.anims.create({
            key: 'door_light',
            frames: this.frames,
            frameRate: 10,
            repeat: -1
        });
        this.anims.play('door_light');

        this.setInteractive();
        this.on('pointerdown', function (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) {
            const moveAction = door.actions.pop();
            store.dispatch('action/executeAction', { target: door, action: moveAction });
            event.stopPropagation(); //Need that one to prevent other effects
        });
    }
}
