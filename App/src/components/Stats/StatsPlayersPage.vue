<template>
    Here you can get informations about players from the database.
    <button class="action-button" @click="getAllSkillCount()">Get the total number of skill learned.</button>
    <select v-model="selectedSkill">
        <option
            v-for="option in skillList"
            :value="option"
            :key=option
        >
            {{ option }}
        </option>
    </select>
    <button class="action-button" @click="getSkillCount()">Get the number of time a given skill was learned.</button>


    <select v-model="selectedCharacter">
        <option
            v-for="option in characterList"
            :value="option"
            :key=option
        >
            {{ option }}
        </option>
    </select>
    <button class="action-button" @click="getCharacterSkillsCount()">Get skills by character.</button>
    <p class="resultBox" v-html="formatText(result)" />
</template>

<script lang="ts">
import { defineComponent } from "vue";
import StatsService from "@/services/admin.stats.service";
import { formatText } from "@/utils/formatText";

export default defineComponent ({
    name: "StatsPlayersPage",
    data() {
        return {
            skillList : [''],
            selectedSkill : '',
            characterList : [''],
            selectedCharacter : '',
            result : 'Nothing'
        };
    },

    methods: {
        formatText,
        getSkillCount():void {
            StatsService.getPlayerSkillCount(this.selectedSkill)
                .then((result) => {
                    this.result = result;
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        getAllSkillCount():void {
            StatsService.getAllPlayerSkillCount()
                .then((result) => {
                    this.result = result;
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        getCharacterSkillsCount():void {
            StatsService.getCharacterSkillsCount(this.selectedCharacter)
                .then((result) => {
                    this.result = result;
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        getSkillList():void {
            StatsService.getPlayerSkillList()
                .then((result) => {
                    this.skillList = result;
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        getCharacterList():void {
            StatsService.getCharacterList()
                .then((result) => {
                    this.characterList = result;
                })
                .catch((error) => {
                    console.error(error);
                });
        }

    },

    beforeMount() {
        this.getSkillList();
        this.getCharacterList();
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
