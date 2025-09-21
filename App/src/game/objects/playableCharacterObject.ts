import DaedalusScene from "@/game/scenes/daedalusScene";

import CharacterObject from "@/game/objects/characterObject";
import { CartesianCoordinates } from "@/game/types";
import { Player } from "@/entities/Player";
import IsometricGeom from "@/game/scenes/isometricGeom";
import { MushPath } from "@/game/scenes/navigationGrid";
import InteractObject from "@/game/objects/interactObject";
import GameObject = Phaser.GameObjects.GameObject;

export default class PlayableCharacterObject extends CharacterObject {
    private isoPath : MushPath;
    private currentMove : number;
    private lastMove: InteractObject | null;
    private moveObjective: CartesianCoordinates | null;
    private characterSpeed = 74; //TODO: Bring it down if alert = no_gravity?

    constructor(scene: DaedalusScene, cart_coords: CartesianCoordinates, isoGeom: IsometricGeom, player: Player)
    {
        super(scene, cart_coords, isoGeom, player);

        this.isoPath = [];
        this.currentMove = -1;
        this.lastMove = null;
        this.moveObjective = null;
    }

    update(): void
    {
        this.movement();
    }

    //this function return an array of direction to follow to get from character position to the pointed coordinates
    updateMovement(pointer: Phaser.Input.Pointer, object : GameObject | null ): MushPath | null
    {
        if (
            this.player.isLyingDown()
            || this.player.room?.type === 'space'
            || this.player.room?.type === 'patrol_ship'
        ) {
            return null;
        }

        let startingPoint = this.getFeetCartCoords().toIsometricCoordinates();
        let finishPoint = (new CartesianCoordinates(pointer.worldX, pointer.worldY)).toIsometricCoordinates();

        let interactEquipment: InteractObject | null = null;
        if (object !== null && object instanceof InteractObject && !(object instanceof CharacterObject)) {
            interactEquipment = object.getInteractibleObject();
            finishPoint = object.getInteractCoordinates(this.navMesh);
        }

        if (this.interactedEquipment !== null && interactEquipment === this.interactedEquipment) {
            startingPoint = this.interactedEquipment.getInteractCoordinates(this.navMesh);
            this.setPositionFromFeet(startingPoint.toCartesianCoordinates());

            if (!this.player.isLyingDown()) {
                this.interactedEquipment = null;
            }
        }

        //find the path in isometric coordinates using navMeshPlugin
        const newPath = this.navMesh.getCharacterPath(startingPoint, finishPoint);

        if (newPath.length > 1) {
            this.isoPath = newPath;
            this.currentMove = 1;
            this.lastMove = null;

            this.moveObjective = finishPoint.toCartesianCoordinates();
            this.setPositionFromFeet(new CartesianCoordinates(newPath[0].cartX, newPath[0].cartY));

            // Character is sitting after walking to the equipment
            if (interactEquipment !== null && interactEquipment.getInteractionInformation()?.sitAutoTrigger) {
                this.lastMove = interactEquipment;
                this.player.isSeated = true;
            } else {
                this.player.isSeated = false;
            }
        }

        return this.isoPath;
    }


    // this function get the first part of the computed path that haven't been completed yet
    // check if the character reached its destination (using a threshold)
    updateCurrentMove(): number
    {
        // if the move is finished
        if (this.isoPath.length === 0) {
            return this.currentMove = -1;
        }

        // if, for any reason, the character left the walking grid stop movement and put the player on the closest point on the grid
        if (this.depth === -1) {
            this.setPositionFromFeet(this.navMesh.getClosestPoint(this.getFeetCartCoords().toIsometricCoordinates()).toCartesianCoordinates());
            this.resetMove();
            return this.currentMove;
        }

        const currentIsoPath = this.isoPath[this.currentMove];

        if (!this.isIsoPathSectionFinished(currentIsoPath)){
            return this.currentMove;
        } else if (this.currentMove < this.isoPath.length - 1) {
            this.setDepth(this.isoPath[this.currentMove+1].depth);
            this.setPositionFromFeet(new CartesianCoordinates(currentIsoPath.cartX ,currentIsoPath.cartY));
            return this.currentMove = this.currentMove +1;
        } else {
            (<Phaser.Physics.Arcade.Body >this.body).stop();

            if (this.lastMove !== null) {
                this.applyEquipmentInteractionInformation(this.lastMove);
                this.lastMove = null;
            } else {
                if (Math.random() > 0.5) {
                    this.flipX = true;
                }
                this.anims.play('right');

                this.checkPositionDepth();
            }

            this.resetMove();

            return this.currentMove;
        }
    }

    isIsoPathSectionFinished(currentIsoPath: { direction: string, cartX: number, cartY: number, depth: number }): boolean
    {
        const currentIsoTarget = new CartesianCoordinates(currentIsoPath.cartX, currentIsoPath.cartY).toIsometricCoordinates();
        const currentIsoPosition = this.getFeetCartCoords().toIsometricCoordinates();

        switch (currentIsoPath.direction) {
        case 'none':
            return true;
        case 'north':
            return currentIsoTarget.y - currentIsoPosition.y >= 0;
        case 'south':
            return currentIsoTarget.y - currentIsoPosition.y <= 0;
        case 'east':
            return currentIsoTarget.x - currentIsoPosition.x <= 0;
        case 'west':
            return currentIsoTarget.x - currentIsoPosition.x >= 0;
        }
        return true;
    }

    // this function apply the computed path
    // moving the sprite and playing the animation
    movement(): void
    {
        const cartSpeed = this.characterSpeed;

        if (this.currentMove !== -1) {
            this.updateCurrentMove();
        }

        if (this.currentMove === -1) {
            return;
        }

        const currentMove = this.isoPath[this.currentMove];

        //if move on EW axis
        if (currentMove.direction === 'west') {
            this.flipX = false;
            // @ts-ignore
            this.body.setVelocityX(-cartSpeed);
            // @ts-ignore
            this.body.setVelocityY(-cartSpeed/2);
            if (this.anims.currentAnim === null || this.anims.currentAnim.key !== 'move_left') {
                this.anims.play('move_left');
            }
        } else if (currentMove.direction === 'east') { //move to the E
            this.flipX = false;
            // @ts-ignore
            this.body.setVelocityX(cartSpeed);
            // @ts-ignore
            this.body.setVelocityY(cartSpeed/2);
            if (this.anims.currentAnim === null || this.anims.currentAnim.key !== 'move_right') {
                this.anims.play('move_right');
            }
        } else if (currentMove.direction === 'south') {//move to the S
            this.flipX = true;
            // @ts-ignore
            this.body.setVelocityX(-cartSpeed);
            // @ts-ignore
            this.body.setVelocityY(cartSpeed/2);
            if (this.anims.currentAnim === null || this.anims.currentAnim.key !== 'move_right') {
                this.anims.play('move_right');
            }
        } else if (currentMove.direction === 'north') {//move to the N
            this.flipX = true;
            // @ts-ignore
            this.body.setVelocityX(cartSpeed);
            // @ts-ignore
            this.body.setVelocityY(-cartSpeed/2);
            if (this.anims.currentAnim === null || this.anims.currentAnim.key !== 'move_left') {
                this.anims.play('move_left');
            }
        }
    }

    getMovementTarget(): CartesianCoordinates | null
    {
        return this.moveObjective;
    }

    resetMove(): void
    {
        this.isoPath = [];
        this.currentMove = -1;
        this.moveObjective = null;
        (<Phaser.Physics.Arcade.Body>this.body).stop();
    }

    applyEquipmentInteraction(): void
    {
        const targetBed = this.player.isLyingDown();
        const room = this.player.room;

        const phaserBed = targetBed !== null ? (<DaedalusScene>this.scene).findObjectByNameAndId(targetBed.key, targetBed.id) : null;

        if (phaserBed) {
            this.applyEquipmentInteractionInformation(phaserBed);
            this.resetMove();
        } else if (room && room.type === 'space') {
            this.play('space_giggle');
        } else {
            if (this.interactedEquipment !== null) {
                const interactCoordinates = this.interactedEquipment.getInteractCoordinates(this.navMesh);
                this.setPositionFromFeet(interactCoordinates.toCartesianCoordinates());
                this.interactedEquipment = null;
            }
            // Set the initial sprite randomly such as it faces the screen
            this.flipX = Math.random() > 0.5;
            this.anims.play('right');
            this.checkPositionDepth();
        }
    }
}
