<template>
    <div class="homepage-container">
        <div class="box-container">
            <p v-html="$t('alpha.homepage')" />
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    name: "HomePage"
});
</script>

<style lang="scss" scoped>

.homepage-container {
    flex-flow: row wrap;
    justify-content: center;
    align-self: center;
    max-width: 1280px;
    margin: 1em 3vw;

    .box-container {
        max-width: calc(640px - 2em);
    }

    p {
        text-align: justify;
        font-size: 1.1em;
        line-height: 1.4;
    }

    img {
        width: auto;
        height: auto;
        margin: 0.6em auto .1em;
        padding: .6em;
        border-radius: .4em;
        background-color: rgba(15, 15, 67, .5);
    }

    &::v-deep a {
        color: $green;
        &:hover { color: #e9ebf3; }
    }

    &::v-deep em {
        color: #01c3df;
        font-size: 1.2em;
        font-style: normal;
        font-weight: bold;

        &.red {
            color: inherit;
            text-decoration: underline;
            text-decoration-color:#ff4e64;
        }
    }
}

</style>
