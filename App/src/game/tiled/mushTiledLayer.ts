import Phaser from "phaser";

export default class MushTiledLayer extends Phaser.Tilemaps.LayerData {
    getCustomPropertyByName(property: string): number
    {
        const existingKeys = ['depth', 'walkingDepth'];
        if (existingKeys.includes(property)) {
            for (let i = 0; i < this.properties.length; i++) {
                //@ts-ignore
                if (this.properties[i].name === property) {
                    //@ts-ignore
                    return this.properties[i].value;
                }
            }
        }
        return -1;
    }
}