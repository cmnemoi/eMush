<template>
    <h1>{{ $t('admin.actions.title') }}</h1>
    <div class="flex-row">
        <button class="action-button" @click="createProjects">
            {{ $t('admin.actions.createProjects') }}
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
            createProjects: 'adminActions/createProjectsForOnGoingDaedaluses'
        })
    }
});
</script>

<style lang="scss" scoped>
</style>