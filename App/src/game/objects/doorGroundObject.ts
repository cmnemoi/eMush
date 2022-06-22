import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import { Door as DoorEntity } from "@/entities/Door";
import store from "@/store";
import { Action } from "@/entities/Action";
import InteractObject from "@/game/objects/interactObject";
import IsometricGeom from "@/game/scenes/isometricGeom";


/*eslint no-unused-vars: "off"*/
export default class DoorGroundObject extends InteractObject {
    protected door: DoorEntity;

    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        tileset: Phaser.Tilemaps.Tileset,
        firstFrame: number,
        isFlipped: { x: boolean, y: boolean},
        door: DoorEntity,
    )
    {
        super(scene, cart_coords, iso_geom, tileset, firstFrame, door.key, isFlipped, true, false);

        this.door = door;

        if (firstFrame === 5 || firstFrame === 15){
            this.setDepth(0);
        } else {
            this.setDepth(this.y + this.width/2);
        }


        this.on('pointerdown', () => {this.onDoorClicked();}, this);

        this.canMove();
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
        console.log('coucou');
        console.log(this.door);
        if(!this.door.isBroken && this.canMove()) {
            //if player click on the door
            const moveAction = this.getMoveAction();
            store.dispatch('action/executeAction', { target: this.door, action: moveAction });
            store.dispatch('room/selectTarget', { target: null });
            store.dispatch('room/closeInventory');
        } else {
            //If the door is broken propose the repair action
            store.dispatch('room/selectTarget', { target: this.door });
        }
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
}
