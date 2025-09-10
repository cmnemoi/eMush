import Phaser from 'phaser';
import daedalusScene from "@/game/scenes/daedalusScene";
import { Player } from "@/entities/Player";
//@ts-ignore
import PhaserNavMeshPlugin from "phaser-navmesh";


function launch(containerId: any, player: Player): Phaser.Game {
    const game = new Phaser.Game({
        type: Phaser.WEBGL,
        width: 424,
        height: 460,
        backgroundColor: '#2d2d2d',
        parent: containerId,
        pixelArt: true,
        roundPixels: true,
        render: {
            antialias: false,
            powerPreference: 'low-power',
            failIfMajorPerformanceCaveat: false,
            premultipliedAlpha: false,
            transparent: false,
            clearBeforeRender: true
        },
        fps: {
            target: 60,
            min: 30,
            smoothStep: true,
            panicMax: 0,
            forceSetTimeOut: false
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
                debug: false,
                fps: 60
            }
        }
    });

    game.events.on(Phaser.Core.Events.BLUR, () => game.loop.sleep());
    game.events.on(Phaser.Core.Events.FOCUS, () => game.loop.wake());
    game.events.on(Phaser.Core.Events.HIDDEN, () => game.loop.sleep());
    game.events.on(Phaser.Core.Events.VISIBLE, () => game.loop.wake());

    return game;
}


export default launch;
export { launch };
