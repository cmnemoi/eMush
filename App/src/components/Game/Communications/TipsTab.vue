<template>
<div class="chatbox-container" id="tips-tab">
  <!-- NOTHING TO SEE HERE YET -->
</div>
</template>

<script>
export default {
  name: "TipsTab",
  props: {
  }
}
</script>

<style lang="scss" scoped>

#tips-tab {
  position: relative;
  overflow: auto;
  z-index: 2;
  height: 436px;
  margin-top: -1px;
  line-height: initial;
  background: rgba(194, 243, 252, 1);
  color: #090a61;
  @include corner-bezel(0px, 6.5px, 0px);

}

/* SCROLLBAR STYLING */

.chatbox-container, {
  --scrollbarBG: white;
  --thumbBG: #090a61;

  scrollbar-width: thin;
  scrollbar-color: var(--thumbBG) var(--scrollbarBG);
  &::-webkit-scrollbar { width: 6px; }
  &::-webkit-scrollbar-track { background: var(--scrollbarBG); }
  &::-webkit-scrollbar-thumb { background-color: var(--thumbBG); }
}

</style>