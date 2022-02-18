import DaedalusScene from "@/game/scenes/daedalusScene";

import CharacterObject from "@/game/objects/characterObject";
import { IsometricCoordinates, CartesianCoordinates } from "@/game/types";
import { Player } from "@/entities/Player";
import IsometricGeom from "@/game/scenes/isometricGeom";
import { DepthElement } from "@/game/scenes/depthSortingArray";

/*eslint no-unused-vars: "off"*/
export default class PlayableCharacterObject extends CharacterObject {
    private isoPath : Array<{ direction: string, cartX: number, cartY: number }>;
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

        this.checkPositionDepth();

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

        /* // @ts-ignore
        this.navMesh.debugDrawPath(path, 0xffd900);;

        const debugGraphics = this.scene.add.graphics().setAlpha(1);
        debugGraphics.fillStyle(0xff0000, 1);
        debugGraphics.lineStyle(1, 0x000000, 1.0);
        if (path !== null){
            for (let i = 1; i < path.length; i++) {
                const isoCoord = new IsometricCoordinates(path[i].x, path[i].y);
                debugGraphics.fillPointShape(isoCoord.toCartesianCoordinates(), 5);
            }
        }*/


        if (path !== null){
            this.isoPath = [];
            this.currentMove = 0;

            //now convert the isometric path into a cartesian path
            for (let i = 1; i < path.length; i++) {


                const deltaEW = path[i].x - path[i-1].x;
                const deltaNS = path[i].y - path[i-1].y;

                let cartPoint = null;
                let direction = 'none';

                //if the character only move NS or EW in the current part of the path
                if (deltaNS === 0 || deltaEW === 0){
                    cartPoint = (new IsometricCoordinates(path[i].x, path[i].y)).toCartesianCoordinates();
                    direction = this.getDirection( new IsometricCoordinates(path[i-1].x, path[i-1].y), new IsometricCoordinates(path[i].x, path[i].y));

                    this.isoPath.push({ "direction": direction, "cartX": cartPoint.x, "cartY": cartPoint.y });


                } else{ //if there is a NS AND EW component to the current part of the path
                    let intermediatePoint = null;
                    //randomly choose if the character is going to complete first the EW of NS component
                    if (Math.random() > 0.5){
                        intermediatePoint = new IsometricCoordinates(path[i].x, path[i - 1].y);
                    } else {
                        intermediatePoint = new IsometricCoordinates(path[i - 1].x, path[i].y);
                    }

                    cartPoint = intermediatePoint.toCartesianCoordinates();
                    direction = this.getDirection(new IsometricCoordinates(path[i-1].x, path[i-1].y), intermediatePoint);
                    this.isoPath.push({ direction: direction, "cartX": cartPoint.x, "cartY": cartPoint.y });

                    cartPoint = (new IsometricCoordinates(path[i].x, path[i].y)).toCartesianCoordinates();
                    direction = this.getDirection(intermediatePoint, new IsometricCoordinates(path[i].x, path[i].y));
                    this.isoPath.push({ "direction": direction, "cartX": cartPoint.x, "cartY": cartPoint.y });
                }
            }
        }

        return this.isoPath;
    }


    //Get direction from two points in isometric format
    // Iso directions    Iso coordinates     Cart coordinates
    //  W   N                                     _x
    //   \ /                  / \                |
    //   / \                 y   x               y
    //  S   E
    getDirection(start: IsometricCoordinates, finish: IsometricCoordinates): string {
        const deltaEW = finish.x - start.x;
        const deltaNS = finish.y - start.y;

        if (deltaNS > 0) {
            return 'south';
        } else if (deltaNS < 0) {
            return 'north';
        } else if (deltaEW > 0) {
            return 'east';
        } else if (deltaEW < 0) {
            return 'west';
        }

        throw new Error('no direction found');
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
            return this.currentMove = this.currentMove +1;
        } else {

            if (Math.random() > 0.5) {
                this.flipX = true;
            }

            this.anims.play('right');

            // @ts-ignore
            this.body.stop();
            this.isoPath = [];

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
