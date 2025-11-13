import Phaser from "phaser";

export default class MushTiledLayer extends Phaser.Tilemaps.LayerData {
    getCustomPropertyByName(property: string): number
    {
        const existingKeys = ['depth', 'walkingDepth'];
        if (existingKeys.includes(property)) {
            for (let i = 0; i < this.properties.length; i++) {
                // @ts-expect-error Phaser did not type correctly its API.
                if (this.properties[i].name === property) {
                    // @ts-expect-error Phaser did not type correctly its API.
                    return this.properties[i].value;
                }
            }
        }
        return -1;
    }
}
