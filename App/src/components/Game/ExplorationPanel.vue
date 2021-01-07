<template>
    <div>
        <h3><img src="@/assets/images/planet.png"><span>Exploration en cours</span></h3>
        <p>
            <span>Planète :</span> Mushebia<br>
            <span>Équipe :</span> Chun , Jin Su<br>
            <span>Retour estimé :</span> 30 minutes
        </p>
    </div>
</template>

<script>
export default {
    name: "ExplorationPanel",
    props: {
    }
};
</script>

<style  lang="scss" scoped>
div {
    align-self: flex-end;
    min-width: auto;
    max-width: 420px;
    padding: 0 7px 3px 7px;
    margin: 0 12px 8px 12px;
    background-color: #191a4c;
    border-radius: 3px;
    border: 1px solid #3965fb;
    box-shadow:
        0 0 0 1px #191a4c,
        0 0 5px 1px rgba(57, 101, 251, 0.7),
        0 0 8px -6px rgba(0, 0, 0, 0.7);

    & h3 {
        display: flex;
        position: relative;
        flex-direction: row;
        align-items: center;
        margin: 0 2em;
        padding: 0 26px;
        min-height: 22px;
        color: #f6d70a;
        font-size: 0.75em;
        font-weight: 400;
        font-variant: small-caps;
        text-shadow: 0 0 4px black, 0 0 4px black;
        background: #2d2f89;
        & img { padding-right: 4px; }

        @include corner-bezel(0, 0, 22px, 22px );
    }

    & p {
        margin: 0.6em 0;
        font-size: 0.75em;
        letter-spacing: 0.02em;
        line-height: 1.25em;

        & span {
            color: #01c3df;
            font-variant: small-caps;
        }
    }
}
</style>
