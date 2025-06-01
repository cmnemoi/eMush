<template>
    <PopUp
        :is-open="popUpData.isOpen"
        @close="closePopUp"
    >
        <h1>{{ popUpData.playerName }}</h1>
        <h2>{{ $t('theEnd.triumph') }}</h2>
        <ul>
            <li v-for="(gain, index) in popUpData.gains" :key="index">
                <span v-html="formatText(gain)" />
            </li>
        </ul>
    </PopUp>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";
import PopUp from "@/components/Utils/PopUp.vue";
import { formatText } from "@/utils/formatText";

export default defineComponent ({
    name: "TriumphGainsPopup",
    components: { PopUp },
    computed: {
        ...mapGetters({
            popUpData: 'popup/triumphGainsPopUp'
        })
    },
    methods: {
        ...mapActions({
            closePopUp: 'popup/closeTriumphGainsPopUp'
        }),
        formatText
    }
});
</script>

<style lang="scss" scoped>
h1 {
    font-size: 1.5em;
    text-transform: uppercase;
    text-align: center;
    margin: 0;
}

h2 {
    font-size: 1.2em;
    text-align: center;
    margin-top: 1em;
    margin-bottom: 1em;
    color: #8E9CBE;
}

ul {
    list-style: disc inside url("/src/assets/images/ui_icons/point.png");
    padding-left: 1em;
    margin-bottom: 1em;
    display: block;
}

li {
    display: list-item;
    margin-bottom: 0.5em;
}
</style>
