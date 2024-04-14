 <template>
    <div class="toast-container">
        <Toast />
    </div>
</template>

<script lang="ts">
import Toast from "@/components/Utils/Toast.vue";
import { defineComponent } from "vue";

export default defineComponent ({
    components: {
        Toast
    },
});
</script>

<style lang="scss" scoped>

.toast-container {
    position: fixed;
    z-index: 999;
    bottom: 1em;
    width: 100%;
    padding: 0 0.8em;
    flex-direction: column-reverse;
    align-items: center;
    row-gap: 1.2em;

    @media screen and (max-width: $breakpoint-desktop-m) {
        font-size: 0.9em;
        row-gap: 0.8em;
    }
}

.code {
    padding: 0.4em 0.8em;
    margin: 0.4em -0.1em;
    border: 1px solid $greyBlue;
    border-radius: 4px;
    background-color: rgba(35, 37, 100, 1);
    box-shadow: inset 0 0 4px $deepBlue;
    // font-family: 'Courier New', Courier, monospace;
    line-height: 1.4em;

    .details { margin-top: 0.4em; }

    strong { letter-spacing: 0.03em; }
}

.toast-background {
    position: fixed;
    background: transparentize(#09092d, 0.5);
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 998;
    transition: all 0.3s;
}

</style>
