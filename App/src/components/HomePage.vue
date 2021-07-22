<template>
    <div class="homepage-container">
        <div class="box">
            <p v-html="$t('alpha.homepage')" />
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    name: "HomePage",
});
</script>

<style lang="scss" scoped>

.homepage-container {
    flex-flow: row wrap;
    justify-content: center;
    align-self: center;
    max-width: 1280px;
    margin: 1em 3vw;

    .box {
        position: relative;
        max-width: calc(640px - 2em);
        margin: 3em 1em;
        padding: 1em 1.5em;

        @include corner-bezel(18.5px);

        box-shadow: inset 0 0 35px 25px rgba(15, 89, 171, 0.5);
        background-color: rgba(34, 38, 102, 0.5);
    }

    p {
        text-align: justify;
        font-size: 15px;
        line-height: 1.4em;
    }

    img {
        width: auto;
        height: auto;
        margin: 0.6em auto .1em;
        padding: .6em;
        border-radius: .4em;
        background-color: rgba(15, 15, 67, .5);
    }

    a {
        color: #84e100;

        &:hover { color: #e9ebf3; }
        &:visited { color: #84e100; }
    }

    em {
        color: #01c3df;
        font-size: 1.2em;
        font-style: normal;
        font-weight: bold;

    //    &.red { color: #ff4e64; }
        &.red {
            color: inherit;
            text-decoration: underline;
            text-decoration-color:#ff4e64;
        }
    }
}

</style>
