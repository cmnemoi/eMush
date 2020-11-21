<template>
<div class="inventory-container">
  <div class="inventory">
    <ul>
      <li class="empty"></li>
      <li class="empty"></li>
      <li class="empty"></li>
      <li class="empty"></li>
      <li class="empty"></li>
      <li class="empty"></li>
      <li class="empty"></li>
      <li class="empty"></li>
      <li class="empty"></li>
      <li class="empty"></li>
      <li class="empty"></li>
      <li class="empty"></li>
      <li class="empty"></li>
    </ul>
  </div>
  <p class="item-name">Bananier</p>
  <div class="item-actions">
    <ul>
      <li><a href="#">Examiner</a></li>
      <li><a href="#">Prendre</a></li>
      <li><a href="#">1 <img src="@/assets/images/pa.png" alt="ap"> Arroser</a></li>
      <li><a href="#">1 <img src="@/assets/images/pa.png" alt="ap"> Cacher</a></li>
      <li><a class="crossed" href="#">2 <img src="@/assets/images/pa.png" alt="ap"> Traiter</a></li>
    </ul>
  </div>
</div>

</template>

<script>
export default {
  name: "RoomInventoryPanel",
  props: {
  }
}
</script>

<style lang="scss" scoped>
.inventory-container {
  z-index: 5;
  position: absolute;
  bottom: 0;
  width: 424px;

  & .inventory {
    overflow: hidden;
  }

  & .inventory ul {
    display: flex;
    flex-direction: row;
    overflow-x: scroll;
    margin: 0 16px 8px 16px;
  
    & li {
      @include inventory-slot();
      margin-bottom: 5px;
    }
  }
  
  & .item-name {
    text-align: center;
    font-size: .85em;
    font-variant: small-caps;
    margin: 0;
    padding: 4px 0 6px 0;
    background: #09092d;
  }

  & .item-actions {
    position: relative;
    min-height: 108px;
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