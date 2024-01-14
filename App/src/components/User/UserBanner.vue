<template>
    <div class="sub-menu">
        <router-link :to="{ name: 'UserShips' }">{{ $t("userBanner.userShips") }} </router-link>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";


export default defineComponent ({
    name: 'UserBanner'
});
</script>

<style lang="scss" scoped>

.sub-menu {
    display: flex;
    flex-direction: row;
    margin: 1.8em auto -1px auto;
    padding: 0 20px;
    max-width: $breakpoint-desktop-l;
    width: 100%;

    a {
        background-color: rgba(15, 89, 171, 0.55);
        min-width: 130px;
        padding: 12px 16px;
        margin-right: 10px;
        color: white;
        text-align: center;
        font-weight: normal;
        letter-spacing: .06em;
        text-transform: uppercase;
        text-decoration: none;

        @include corner-bezel(14px, 14px, 0);

        opacity: .8;

        &:hover,
        &:active {
            opacity: 1;
        }
    }
}

</style>
