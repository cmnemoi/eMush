<template>
    <ul>
        <li v-for="(value, key) in errors" :key="key">{{ value }}</li>
    </ul>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    name: "ErrorList",
    props: {
        errors: Array
    }
});
</script>

<style lang="scss" scoped>
    ul {
        color: red;
    }
</style>
