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

    &.right-align .dropdown-content {
        right: 0;
        // align-items: flex-end;
    }

    button {
        @include button-style();
        padding-left: 1em;
        padding-right: 1em;;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
    }
}

.dropdown-content {
    position: absolute;
    display: flex;
    visibility: hidden;
    z-index: 2;
    min-width: 100%;
    width: max-content;
    // padding: .4em;
    // background: #222b6b;
    // border-radius: 5px;
    box-shadow: 0px 10px 10px 0px rgba(0,0,0,0.4);

    // transition: visibility .4s;

    &::v-deep(button) { width: auto; }
}

.dropdown:hover,
.dropdown:focus,
.dropdown:focus-within {
    .dropdown-content { visibility: visible; }
}

</style>