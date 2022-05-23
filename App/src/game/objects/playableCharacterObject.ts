import DaedalusScene from "@/game/scenes/daedalusScene";

import CharacterObject from "@/game/objects/characterObject";
import { IsometricCoordinates, CartesianCoordinates } from "@/game/types";
import { Player } from "@/entities/Player";
import IsometricGeom from "@/game/scenes/isometricGeom";
import { DepthElement } from "@/game/scenes/depthSortingArray";
import { MushPath } from "@/game/scenes/navigationGrid";

/*eslint no-unused-vars: "off"*/
export default class PlayableCharacterObject extends CharacterObject {
    private isoPath : MushPath;
    private currentMove : number;
    private indexDepthArray: number;

    constructor(scene: DaedalusScene, cart_coords: CartesianCoordinates, isoGeom: IsometricGeom, player: Player)
    {
        super(scene, cart_coords, isoGeom, player);

        this.isoPath = [];
        this.currentMove = -1;
        this.indexDepthArray = 0;


        this.scene.input.on('pointerdown', (pointer: Phaser.Input.Pointer) => {
            this.getCartPath(pointer);
        }, this);
    }

    update(): void
    {
        this.movement();

        // const debugGraphics = this.scene.add.graphics().setAlpha(1);
        // debugGraphics.fillStyle(0xfbff00, 1);
        // debugGraphics.fillPointShape(this.getFeetCartCoords(), 5);
    }



    //this function return an array of direction to follow to get from character position to the pointed coordinates
    getCartPath(pointer: Phaser.Input.Pointer): Array<{ direction: string, cartX: number, cartY: number }>
    {
        const startingPoint = this.getFeetCartCoords().toIsometricCoordinates();
        const finishPoint = (new CartesianCoordinates(pointer.worldX, pointer.worldY)).toIsometricCoordinates();


        //find the path in isometric coordinates using navMeshPlugin
        const path = this.navMesh.findPath({ x: startingPoint.x, y: startingPoint.y }, { x: finishPoint.x, y: finishPoint.y });

        // // naMesh debug
        // // @ts-ignore
        // this.navMesh.debugDrawPath(path, 0xffd900, 2);
        // const debugGraphics = this.scene.add.graphics().setAlpha(1);
        // debugGraphics.fillStyle(0x00FF00, 1);
        // debugGraphics.lineStyle(3, 0x00FF00, 1.0);
        // if (path !== null){
        //     for (let i = 1; i < path.length; i++) {
        //         const isoCoord = new IsometricCoordinates(path[i].x, path[i].y);
        //         debugGraphics.fillPointShape(isoCoord.toCartesianCoordinates(), 5);
        //     }
        // }

        if (path !== null) {
            this.isoPath = (<DaedalusScene>this.scene).navMeshGrid.convertNavMeshPathToMushPath(path);
            this.currentMove = 0;
        }

        return this.isoPath;
    }





    // this function get the first part of the computed path that haven't been completed yet
    // check if the character reached its destination (using a threshold)
    updateCurrentMove(): number
    {
        const displacementThreshold = 4;

        const distance = Math.sqrt(
            Math.pow(this.isoPath[this.currentMove].cartX - this.x, 2) +
            Math.pow(this.isoPath[this.currentMove].cartY - this.getFeetCartCoords().y, 2)
        );


        if (Math.abs(distance) > displacementThreshold){
            return this.currentMove;
        } else if (this.currentMove < this.isoPath.length - 1) {
            this.setDepth(this.isoPath[this.currentMove+1].depth);
            return this.currentMove = this.currentMove +1;
        } else {

            if (Math.random() > 0.5) {
                this.flipX = true;
            }

            this.anims.play('right');

            // @ts-ignore
            this.body.stop();
            this.isoPath = [];

            this.checkPositionDepth();
            return this.currentMove = -1;
        }
    }


    // this function apply the computed path
    // moving the sprite and playing the animation
    movement(): void
    {
        //Would it be possible to use variables instead of array? :)
        const cartSpeed = { x: 50, y: 25 };

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
            this.body.setVelocityX(-cartSpeed.x);
            // @ts-ignore
            this.body.setVelocityY(-cartSpeed.y);
            if (this.anims.currentAnim.key !== 'move_left') {
                this.anims.play('move_left');
            }


        } else if (currentMove.direction === 'east') { //move to the E
            this.flipX = false;
            // @ts-ignore
            this.body.setVelocityX(cartSpeed.x);
            // @ts-ignore
            this.body.setVelocityY(cartSpeed.y);
            if (this.anims.currentAnim.key !== 'move_right') {
                this.anims.play('move_right');
            }


        } else if (currentMove.direction === 'south') {//move to the S
            this.flipX = true;
            // @ts-ignore
            this.body.setVelocityX(-cartSpeed.x);
            // @ts-ignore
            this.body.setVelocityY(cartSpeed.y);
            if (this.anims.currentAnim.key !== 'move_right') {
                this.anims.play('move_right');
            }


        } else if (currentMove.direction === 'north') {//move to the N
            this.flipX = true;
            // @ts-ignore
            this.body.setVelocityX(cartSpeed.x);
            // @ts-ignore
            this.body.setVelocityY(-cartSpeed.y);
            if (this.anims.currentAnim.key !== 'move_left') {
                this.anims.play('move_left');
            }

        }
    }
}
