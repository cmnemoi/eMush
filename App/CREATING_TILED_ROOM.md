# Creating Tiled room

Please read this guide carefully if you plan to add a visual interface for a room in e-Mush

## Tiled

Tiled is a free software designed to create game scene from tile sets (a set of small images).

More information and download on https://www.mapeditor.org/

## eMush room format

eMush requires the room to be provided in a .json placed in _mush/App/scr/game/assets_

The .json file should then be referenced in _mush/App/scr/game/scenes/daedalusScene.ts_.

Tiled allows to create .json files from mush assets. However, due to poor support of isometric view both in Tiled and Phaser, several recommendations should be followed in order to get functional rooms.

## What is the difference between isometric and cartesian coordinates ?

Iso directions........Iso coordinates.......Cart coordinates

...... W ..... N ................................................ _ x ...........

......... \ .. / ...................... / .. \ ...................... | ...............

......... / .. \ .................... y ..... x ................... y ..............

...... S ..... E ...................................................................


## 1. Creating tilesets files

Check if all needed images are in _mush/App/scr/game/assets/tilemaps_.

If not, look for the needed assets in https://gitlab.com/eternaltwin/mush/assets

For new tilesets you will need to edit them in an image editor software (e.g. Gimp) to create a set of images of the same size put one after the other without any blank pixel between them.

## 2. Adding a tileset in Tiled

Simply drag and drop the .png file in the tileset window of Tiled. Size of the images of the tileset is required in px.

**Before placing any tile of the tileset** : in Tiled, right-click on the tileset and open its properties and set `object alligment` on `bottom left`

Is there is the tileset is animated, this is the time to create the animation within Tiled.

## 3. Creating the skeleton of the room

The basic view of the room consists of three layers : the ground, the walls and the back walls (walls that are deeper than doors).

## 4. Creating objects

Everything that is neither wall nor ground should be added as a Tiled object.

If the object is animated ass the animation (not just 1 frame).

There is 4 types of objects, the type should be specified in the Type attribute of the object:

* _door_ and _door_ground_: respectively for doors and lighted path in front of doors.
* _interact_: object that can be interacted with (equipments).
* _shelf_: the room shelf.
* _decoration_: all other objects.

For _door_, _door_ground_ and _interact_, the name attribute should be filed with the same string as the one provided in _mush/Api/scr/Place/Enum/DoorEnum_ and _mush/Api/scr/Equipment/Enum/EquipmentEnum_.

For _interact_, _decoration_ and _shelf_, you need to add a custom property to the object named _collides_ it is either True of False.

For _interact_, _decoration_ and _shelf_, you need to add two custom properties (_isoSizeX_ and _isoSizeY_) that indicates the size of the object in pixel in isometric coordinates. For reference a ground tile is 16 px by 16 px.
