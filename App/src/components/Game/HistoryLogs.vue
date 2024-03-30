<template>
    <div class="history-logs">
        <span class="tab">Grands Moments</span>
        <span class="tab active">Historique de Triomphe</span>
        <div class="logs">
            Not implemented yet ...

            <!--            <p><img :src=getImgUrl('point.png')" alt="dot"> 2 x Cycle Humain ( + 1 )</p>-->
            <!--            <p><img :src=getImgUrl('point.png')" alt="dot"> 1 x Ambitieux ( + 4 )</p>-->
            <!--            <p><img :src=getImgUrl('point.png')" alt="dot"> 1 x Contact Avec Sol ( + 8 )</p>-->
            <!--            <p><img :src=getImgUrl('point.png')" alt="dot"> 1 x Vers le Nouvel Eden ( + 6 )</p>-->
            <!--            <p><img :src=getImgUrl('point.png')" alt="dot"> 3 x Cycle Humain ( + 1 )</p>-->
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent ({
    name: "HistoryLogs",
    methods: {
        getImgUrl
    }
});
</script>

<style lang="scss" scoped>
.history-logs {
    display: block;
}

.tab {
    display: block;
    float: left;
    cursor: pointer;
    font-size: .86em;
    opacity: .6;
    padding: 5px 15px;
    border-radius: 5px 5px 0 0;

    &:hover, &:focus, &:active, &.active { opacity: 1; }

    &.active {
        background: #222b6b;
        border-top: 1px solid #3d4dbf;
    }
}

.logs {
    width: 100%;
    border-top: 1px solid #3d4dbf;
    font-size: .8em;
    letter-spacing: .05em;
    padding: 1em;

    p { margin: .5em 0; }
}
</style>
