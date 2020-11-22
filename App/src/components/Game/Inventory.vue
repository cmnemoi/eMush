<template>
  <ul class="inventory">
    <li v-for="(item) in items" class="slot" v-bind:key="item.id" @click="$emit('select',item)">
      <img :src="itemImage(item)" :alt="item.name">
    </li>
    <li v-for="n in emptySlots" class="empty" v-bind:key="n"></li>
  </ul>
</template>

<script>
import {itemEnum} from "@/enums/item";

export default {
  name: "Inventory",
  props: {
    items: Array,
    minSlot: Number
  },
  computed: {
    emptySlots: function () {
      const emptySlots = (this.minSlot - this.items.length);
      return emptySlots < 0 ? 0 : emptySlots;
    }
  },
  methods: {
    itemImage: function(item) {
      return itemEnum[item.key] ? itemEnum[item.key].image : require('@/assets/images/items/todo.jpg');
    },
  }
}
</script>

<style lang="scss" scoped>
.inventory ul {
  display: flex;
  flex-direction: row;

  li {
    @include inventory-slot();
  }
}
</style>