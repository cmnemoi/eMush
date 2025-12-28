<template>
    Here you can get informations about the mush from the database.
    <label><input type="number" v-model="daedalusIdFirst" min="0"/>Enter Daedalus ID, will check all data since this one. </label>
    <label><input
        type="number"
        v-model="daedalusIdLast"
        :min="daedalusIdFirst"
        :max="daedalusIdFirst + 50"/>Enter Daedalus ID, will check all data until this one. </label>
    <button class="action-button" @click="getMushtData()">Get mush data.</button>
    <p class="resultBox" v-html="formatText(result)"></p>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import StatsService from "@/services/admin.stats.service";
import { formatText } from "@/utils/formatText";

export default defineComponent ({
    name: "StatsMushPage",
    data() {
        return {
            result : 'Nothing',
            daedalusIdFirst : 1,
            daedalusIdLast : 1
        };
    },

    methods: {
        formatText,
        getMushtData():void {
            StatsService.getMushtData(this.daedalusIdFirst, this.daedalusIdLast)
                .then((result) => {
                    this.result = result;
                })
                .catch((error) => {
                    console.error(error);
                });
        }

    }
});

</script>

<style lang="scss" scoped>
    .resultBox {
        background-color: $deepBlue;
        min-height: 100px;

        margin: 5px;

        border-style: solid;
        border-width: thin;
        border-radius: 10px;
        border-color: $darkGrey;

        text-indent: 4px each-line;
        line-height: 150%;
    }


</style>
