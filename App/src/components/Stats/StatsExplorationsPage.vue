<template>
    Here you can get informations about explorations from the database.
    <button class="action-button" @click="getExploFightData()">Get fights data.</button>
    <label><input type="number" v-model="daedalusId"/>Enter Daedalus ID, will check all data since this one. </label>
    <p class="resultBox" v-html="formatText(result)"></p>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import StatsService from "@/services/admin.stats.service";
import { formatText } from "@/utils/formatText";

export default defineComponent ({
    name: "StatsExplorationsPage",
    data() {
        return {
            result : 'Nothing',
            daedalusId : 0
        };
    },

    methods: {
        formatText,
        getExploFightData():void {
            StatsService.getExploFightData(this.daedalusId)
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
