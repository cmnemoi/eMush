<template>
    <h1>{{ $t('admin.actions.title') }}</h1>
    <h2>{{ $t('admin.actions.projects') }}</h2>
    <div class="flex-row">
        <button class="action-button" @click="createProjects">
            {{ $t('admin.actions.createProjects') }}
        </button>
        <button class="action-button" @click="proposeProjects">
            {{ $t('admin.actions.proposeProjects') }}
        </button>
    </div>
    <h2>{{$t('admin.actions.equipment') }}</h2>
    <div class="flex-row">
        <Input :label="$t('admin.actions.equipmentName')" type="text" v-model="createEquipmentDto.equipmentName" />
        <Input :label="$t('admin.actions.equipmentQuantity')" type="number" v-model="createEquipmentDto.quantity" />
        <Input :label="$t('admin.actions.equipmentPlace')" type="text" v-model="createEquipmentDto.place" />
        <button class="action-button" @click="createEquipment(createEquipmentDto)">
            {{ $t('admin.actions.createEquipment') }}
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
            }
        };
    },
    methods: {
        ...mapActions({
            createProjects: 'adminActions/createProjectsForOnGoingDaedaluses',
            createEquipmentForOnGoingDaedaluses: 'adminActions/createEquipmentForOnGoingDaedaluses',
            proposeProjects: 'adminActions/proposeNewNeronProjectsForOnGoingDaedaluses'
        }),
        createEquipment(createEquipmentDto: CreateEquipmentDto) {
            this.createEquipmentForOnGoingDaedaluses(createEquipmentDto);
        }
    }
});
</script>

<style lang="scss" scoped>
</style>
