import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { characterEnum, CharacterInfos } from "@/enums/character";
import { CartesianCoordinates, IsometricDistance, toIsometricCoords } from "@/game/types";
import { Player } from "@/entities/Player";
import store from "@/store";
import { PhaserNavMesh } from "phaser-navmesh/src";

export default class CharacterObject extends Phaser.GameObjects.Sprite {
    protected sceneAspectRatio : IsometricDistance;
    protected player : Player;
    protected navMesh: PhaserNavMesh;

    constructor(scene: DaedalusScene, cart_coords: CartesianCoordinates, sceneAspectRatio: IsometricDistance, player: Player) {
        super(scene, cart_coords.x, cart_coords.y, player.character.key);
        scene.physics.world.enable(this);

        this.scene = scene;
        this.navMesh = scene.navMesh;
        this.player = player;

        let characterFrames: CharacterInfos = characterEnum[player.character.key];

        if (!characterFrames.moveLeftFirstFrame){
            characterFrames = characterEnum["default"];
        }

        this.scene.add.existing(this);
        this.setInteractive();
        this.sceneAspectRatio = sceneAspectRatio;

        const iso_coords = toIsometricCoords({ x: this.x, y: this.getFeetY() });
        //the first sprite to be displayed are the ones on the last row of either x or y isometric coordinates
        //a second order sorting is applied using the y axis of cartesian coordinates
        //              4
        //            3   3
        //          2   3    2
        //       1    2   2    1           / \
        //          1   2   1             y   x
        //              1
        //
        this.setDepth(Math.max(iso_coords.x + this.sceneAspectRatio.x, iso_coords.y + this.sceneAspectRatio.y) * 1000 + this.getFeetY());


        this.createAnimations(characterFrames);


        //Set the initial sprite randomly such as it faces the screen
        if (Math.random() > 0.5) {
            this.flipX = true;
        }
        this.anims.play('right');


        //If this is clicked then:
        this.on('pointerdown', function (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: Event) {
            store.dispatch('room/selectTarget', { target: player });
            event.stopPropagation(); //Need that one to prevent other effects
        });
        //if clicked outside
        this.scene.input.on('pointerdown', function(){
            store.dispatch('room/selectTarget', { target: null });
        });
    }



    createAnimations(characterFrames: CharacterInfos): void
    {
        this.anims.create({
            key: 'move_left',
            frames: this.anims.generateFrameNames('character', { start: characterFrames.moveLeftFirstFrame, end: characterFrames.moveLeftLastFrame }),
            frameRate: 10,
            repeat: -1
        });
        this.anims.create({
            key: 'up',
            frames: [{ key: 'character', frame: characterFrames.leftFrame }],
            frameRate: 1
        });
        this.anims.create({
            key: 'down',
            frames: [{ key: 'character', frame: characterFrames.rightFrame }],
            frameRate: 1
        });
        this.anims.create({
            key: 'left',
            frames: [{ key: 'character', frame: characterFrames.leftFrame }],
            frameRate: 1
        });
        this.anims.create({
            key: 'right',
            frames: [{ key: 'character', frame: characterFrames.rightFrame }],
            frameRate: 1
        });
        this.anims.create({
            key: 'move_right',
            frames: this.anims.generateFrameNames('character', { start: characterFrames.moveRightFirstFrame, end: characterFrames.moveRightLastFrame }),
            frameRate: 10,
            repeat: -1
        });
    }



    getFeetY(): number
    {
        const tileHeight = 16;

        return (this.y + this.height / 2 - tileHeight/2);
    }
}
