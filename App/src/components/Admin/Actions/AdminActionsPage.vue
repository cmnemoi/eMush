<template>
    <h1>{{ $t('admin.actions.title') }}</h1>
    <div class="flex-row">
        <button class="action-button" @click="createProjects">
            {{ $t('admin.actions.createProjects') }}
        </button>
        <button class="action-button" @click="proposeProjects">
            {{ $t('admin.actions.proposeProjects') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { mapActions } from "vuex";

export default defineComponent ({
    name: 'AdminActionsPage',
    methods: {
        ...mapActions({
            createProjects: 'adminActions/createProjectsForOnGoingDaedaluses',
            proposeProjects: 'adminActions/proposeNewNeronProjectsForOnGoingDaedaluses'
        })
    }
});
</script>

<style lang="scss" scoped>
</style>
