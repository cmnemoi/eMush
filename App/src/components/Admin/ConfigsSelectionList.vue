<template>
    <div class="configs-list">
        <div class="toolbar">
            <label>
                Rechercher:
                <input type="search">
            </label>
        </div>
        <ul>
            <li class="selected"> <!-- selected configs should display ontop the list, dynamically -->
                <label>
                    <input
                        type="checkbox"
                        id="config_1"
                        name="config_1"
                        checked />
                    <div>
                        <h3>Config 1</h3>
                        <p>Details, comments</p>
                    </div>
                </label>
            </li>
            <li class="selected">
                <label>
                    <input
                        type="checkbox"
                        id="config_2"
                        name="config_2"
                        checked />
                    <div>
                        <h3>Config 2</h3>
                        <p>Details, comments</p>
                    </div>
                </label>
            </li>
            <li>
                <label>
                    <input
                        type="checkbox"
                        id="config_3"
                        name="config_3" />
                    <div>
                        <h3>Config 3</h3>
                        <p>Details, comments</p>
                    </div>
                </label>
            </li>
            <li>
                <label>
                    <input
                        type="checkbox"
                        id="config_4"
                        name="config_4" />
                    <div>
                        <h3>Config 4</h3>
                        <p>Details, comments</p>
                    </div>
                </label>
            </li>
            <li>
                <label>
                    <input
                        type="checkbox"
                        id="config_5"
                        name="config_5" />
                    <div>
                        <h3>Config 5</h3>
                        <p>Details, comments</p>
                    </div>
                </label>
            </li>
        </ul>
        <div class="actions">
            <button>
                Valider
            </button>
            <button>
                Annuler
            </button>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    name: "ConfigsSelectionList"
});

</script>

<style lang="scss" scoped>

.configs-list {
    margin: 1rem 15%;
    border: 1px solid black;
    padding: 1.2em 2.4em;
}

ul {
    flex-direction: column;
    overflow-y: scroll;
    background-color: rgba(0, 0, 0, 0.2);
    border: 1px solid $blue;
}

li {
    display: flex;
    cursor: pointer;
    border-bottom: 1px solid $blue;

    transition: background-color 0.2s;

    &.selected {
        background-color: transparentize($greyBlue, 0.5);
    }

    &:hover, &:focus, &:active {
        background-color: $greyBlue;
    }

    label {
        display: flex;
        flex-direction: row;
        align-items: stretch;
        width: 100%;
        cursor: pointer;
        padding: 1.2rem 1.5rem;
    }

    input[type=checkbox] {
        cursor: pointer;
        margin-right: 1.2rem;
        width: 1.4rem;
    }

    h3 {
        font-size: 1.4rem;
        margin: 0;
    }

    p {
        margin: 0.3em 0 0;
    }
}

.toolbar {
    flex-direction: row;
    justify-content: flex-end;
    padding: .8em 0;
}

.actions {

    flex-direction: row;
    justify-content: center;
    padding: 1.5em 0;

    a, button {
        @include button-style();
        margin: 1px 0.3em;
    }
}

</style>
