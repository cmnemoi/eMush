import Phaser from 'phaser';
import daedalusScene from "@/game/scenes/daedalusScene";
import { PhaserNavMeshPlugin } from "phaser-navmesh/src";
import { Player } from "@/entities/Player";


function launch(containerId: any, player: Player): Phaser.Game {
    return new Phaser.Game({
        type: Phaser.WEBGL,
        width: 424,
        height: 460,
        backgroundColor: '#2d2d2d',
        parent: containerId,
        pixelArt: true,
        fps: {
            target: 60,
            forceSetTimeOut: true
        },
        plugins: {
            scene: [
                {
                    key: "PhaserNavMeshPlugin", // Key to store the plugin class under in cache
                    plugin: PhaserNavMeshPlugin, // Class that constructs plugins
                    mapping: "navMeshPlugin", // Property mapping to use for the scene, e.g. this.navMeshPlugin
                    start: true
                }
            ]
        },
        scene: [new daedalusScene(player)],
        physics: {
            default: 'arcade',
            arcade: {
                debug: false
            }
        }
    });
}


export default launch;
export { launch };
