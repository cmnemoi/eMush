<template>
    <div class="footer">
        <button
            v-for="(language, key) in supportedLanguages"
            :key="key"
            class="language-button"
            @click="() => setUserLanguage(language)"
        >
            {{ language }}
        </button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            supportedLanguages: ["en", "es", "fr"]
        };
    }
};
</script>

<style lang="scss" scoped>

.footer {
    flex-direction: row;
    justify-content: flex-end;
    padding: 0 20px;
}

.language-button {
    cursor: pointer;

    @include button-style();
}

</style>
