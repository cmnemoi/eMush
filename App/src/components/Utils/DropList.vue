<template>
    <div class="dropdown">
        <button>{{ name }} ▼</button>
        <div class="dropdown-content">
            <slot />
        </div>
    </div>
</template>

<script lang="ts">

import { defineComponent } from "vue";

export default defineComponent ({

    name: "DropList",
    props: {
        name: {
            type: String,
            default: "Actions"
        }
    }
});

</script>

<style  lang="scss" scoped>

.dropdown {
    position: relative;
    display: inline-block;
    align-items: stretch;

    &.align-right .dropdown-content {
        right: 0;
        // align-items: flex-end;
    }

    button {
        @include button-style();
        padding: .3em 1em;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }
}

.dropdown-content {
    position: absolute;
    display: flex;
    visibility: hidden;
    z-index: 2;
    min-width: 100%;
    width: max-content;
    padding: 0 2px;
    background: darken(#222b6b, 3%);
    box-shadow: 0px 5px 8px transparentize(black, 0.5);

    // transition: visibility .4s;

    &::v-deep(*) {
        @include button-style();
        width: 100%;
        padding: .4em 1em;
    }

}

.dropdown:hover,
.dropdown:focus,
.dropdown:focus-within {
    .dropdown-content { visibility: visible; }
}

</style>
