<template>
    <tippy class="tippy-tooltip">
      <slot name='tooltip-trigger'></slot>

      <template #content>
          <slot name='tooltip-content'></slot>
      </template>
    </tippy>
</template>

<script>
    export default {
    name: "Tooltip",
    }
</script>