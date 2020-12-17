<template>
<div class="inventory-container">
  <div class="inventory">
    <inventory :items="items" :min-slot="7" v-on:select="selectItem"></inventory>
  </div>
  <p class="item-name" v-if="selectedItem !== null">
    {{ selectedItem.name }}<img src="@/assets/images/status/heavy.png"><img src="@/assets/images/status/plant_thirsty.png"><img src="@/assets/images/status/charge.png">x4
  </p>
  <div class="item-actions">
    <ul v-if="selectedItem !== null">
      <li v-for="(action,key) in selectedItem.actions" v-bind:key="key">
        <a href="#" @click="executeAction(action)">
        <span v-if="action.actionPointCost > 0">{{action.actionPointCost}}<img src="@/assets/images/pa.png" alt="ap"></span>{{action.name}}
        </a>
      </li>
    </ul>
  </div>
</div>

</template>

<script>
import Inventory from "@/components/Game/Inventory";
import ActionService from "@/services/action.service";
import {mapActions} from "vuex";

export default {
  name: "RoomInventoryPanel",
  components: {Inventory},
  props: {
    items: Array
  },
  data: () => {
      return {
        selectedItem: null
      }
  },
  methods: {
    selectItem: function(item) {
      this.selectedItem = item;
    },
    executeAction: function(action) {
      ActionService.executeItemAction(this.selectedItem, action).then(() => this.reloadPlayer());
    },
    ...mapActions('player', [
      'reloadPlayer',
    ]),
  }
}
</script>

<style lang="scss" scoped>
  .slot {
    padding: 1px;
  }

.inventory-container {
  z-index: 5;
  position: absolute;
  bottom: 0;
  width: 424px;

  & .inventory {
    overflow: hidden;
  }

  & .inventory ul {
    overflow-x: scroll;
    margin: 0 16px 8px 16px;
  }
  
  & .item-name {
    text-align: center;
    font-size: .85em;
    font-variant: small-caps;
    margin: 0;
    padding: 4px 0 6px 0;
    background: #09092d;
    img { vertical-align: middle; margin-left: 2px; }
  }

  & .item-actions {
    position: relative;
    //min-height: 108px;
    background: #222a6b;

    &::before {
      content: "";
      position: absolute;
      top: 0;
      left: calc(50% - 8px);
      width: 0px;
      height: 0px;
      border-top: 8px solid #09092d;
      border-left: 8px solid transparent;
      border-right: 8px solid transparent;
    }

    & ul {
      display: flex;
      flex-direction: row;
      flex-wrap: wrap;
      margin: 12px 4px;

      & li a {
        @include button-style();
        min-height: 19px;
        min-width: 200px;
        width: auto;
        margin: 1px 4px;
      }
    }
  }

/* SCROLLBAR STYLING */

  & .inventory ul, {
    --scrollbarBG: rgba(0, 0, 0, .4);
    --thumbBG: rgba(0, 116, 223, 1);
    --border-radius: 4px;

    scrollbar-width: thin;
    scrollbar-color: var(--thumbBG) var(--scrollbarBG);
    &::-webkit-scrollbar { height: 8px; border-radius: var(--border-radius); }
    &::-webkit-scrollbar-track { background: var(--scrollbarBG); border-radius:  var(--border-radius); }
    &::-webkit-scrollbar-thumb { background-color: var(--thumbBG); border-radius:  var(--border-radius); }
  }
}

</style>