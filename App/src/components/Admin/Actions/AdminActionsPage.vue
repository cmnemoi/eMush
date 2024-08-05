<template>
    <h1>{{ $t('admin.actions.title') }}</h1>
    <h2>{{$t('admin.actions.equipment.title') }}</h2>
    <div class="flex-row">
        <Input :label="$t('admin.actions.equipment.name')" type="text" v-model="createEquipmentDto.equipmentName" />
        <Input :label="$t('admin.actions.equipment.quantity')" type="number" v-model="createEquipmentDto.quantity" />
        <Input :label="$t('admin.actions.equipment.place')" type="text" v-model="createEquipmentDto.place" />
        <button class="action-button" @click="createEquipment(createEquipmentDto)">
            {{ $t('admin.actions.equipment.create') }}
        </button>
    </div>
    <h2>{{ $t('admin.actions.projects.title') }}</h2>
    <div class="flex-row">
        <button class="action-button" @click="createProjects">
            {{ $t('admin.actions.projects.create') }}
        </button>
        <button class="action-button" @click="proposeProjects">
            {{ $t('admin.actions.projects.propose') }}
        </button>
    </div>
    <h2>{{$t('admin.actions.statuses.title') }}</h2>
    <div class="flex-row">
        <Input :label="$t('admin.actions.statuses.name')" type="text" v-model="deleteAllStatusesDto.statusName" />
        <button class="action-button" @click="deleteAllStatuses(deleteAllStatusesDto)">
            {{ $t('admin.actions.statuses.deleteAllByName') }}
        </button>
    </div>
    <div class="flex-row">
        <button class="action-button" @click="createStatuses">
            {{ $t('admin.actions.statuses.createAllPlayersInitStatuses') }}
        </button>
    </div>
    <h2>{{$t('admin.actions.rules.title') }}</h2>
    <div class="flex-row">
        <button class="action-button" @click="resetRulesAcceptance">
            {{ $t('admin.actions.rules.resetAcceptance') }}
        </button>
    </div>
    <div class="flex-row">
        <Input :label="$t('nom de la compétence à supprimer')" type="text" v-model="deleteSkillForPlayerDto.skill" />
        <Input :label="$t('id du player')" type="number" v-model="deleteSkillForPlayerDto.playerId" />
        <button class="action-button" @click="deletePlayerSkill(deleteSkillForPlayerDto)">
            {{ $t('supprimer la compétence du joueur') }}
        </button>
    </div>
</template>

<script lang="ts">
import Input from "@/components/Utils/Input.vue";
import { defineComponent } from "vue";
import { mapActions } from "vuex";

interface CreateEquipmentDto {
    equipmentName: string;
    quantity: integer;
    place: string;
}

interface DeleteAllStatusesDto {
    statusName: string;
}

interface DeleteSkillForPlayerDto {
    playerId: integer;
    skill: string;
}

export default defineComponent ({
    name: 'AdminActionsPage',
    components: {
        Input
    },
    data() {
        return {
            createEquipmentDto: {
                equipmentName: 'metal_scraps',
                quantity: 1,
                place: 'laboratory'
            },
            deleteAllStatusesDto: {
                statusName: 'mush'
            },
            deleteSkillForPlayerDto: {
                playerId: 0,
                skill: ''
            }
        };
    },
    methods: {
        ...mapActions({
            createEquipmentForOnGoingDaedaluses: 'adminActions/createEquipmentForOnGoingDaedaluses',
            createProjects: 'adminActions/createProjectsForOnGoingDaedaluses',
            createStatuses: 'adminActions/createPlayersAllInitStatusesForOnGoingDaedaluses',
            deleteAllStatusesByName: 'adminActions/deleteAllStatusesByName',
            deleteSkillForPlayer: 'adminActions/deleteSkillForPlayer',
            proposeProjects: 'adminActions/proposeNewNeronProjectsForOnGoingDaedaluses',
            resetRulesAcceptance: 'adminActions/resetRulesAcceptanceForAllUsers'
        }),
        createEquipment(createEquipmentDto: CreateEquipmentDto) {
            this.createEquipmentForOnGoingDaedaluses(createEquipmentDto);
        },
        deleteAllStatuses(deleteAllStatusesDto: DeleteAllStatusesDto) {
            this.deleteAllStatusesByName(deleteAllStatusesDto);
        },
        deletePlayerSkill(deleteSkillForPlayerDto: DeleteSkillForPlayerDto) {
            this.deleteSkillForPlayer(deleteSkillForPlayerDto);
        }
    }
});
</script>

<style lang="scss" scoped>
</style>
