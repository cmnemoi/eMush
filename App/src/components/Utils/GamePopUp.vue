<template>
    <div class="modal-box">
        <h1 class="header">Popup header</h1>
        <p>Popup text</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque et arcu sit amet arcu convallis dictum. Nam vehicula gravida pretium. Nunc nec malesuada turpis. Vivamus vitae orci leo. Suspendisse potenti. In eu felis quam. Nullam non urna quis sem molestie blandit sit amet eget tortor. Duis aliquet eros quis tempor ornare. Maecenas eu semper quam. Cras malesuada bibendum leo, vel mollis lectus lacinia eget. Nam et lorem eu diam consectetur tempus. Proin fermentum dolor in ullamcorper fermentum. Sed consequat ex vel odio tempus, vitae scelerisque ante tristique.</p>
        <button class="modal-close">
            <img src="@/assets/images/comms/close.png" alt="close">
        </button>
    </div>
</template>

<script>
export default {
    name: "GamePopUp",
    props: {
    }
};
</script>

<style  lang="scss" scoped>
.modal-box {
    position: relative;
    margin-bottom: 1.2em;
    padding: 0 .8em .5em;
    background-color: #191a4c;
    border-radius: 3px;
    border: 1px solid #3965fb;
    box-shadow:
        0 0 0 1px #191a4c,
        0 8px 8px -5px transparentize(black, .15),
        0 0 5px 1px rgba(57, 101, 251, 0.7)
    ;

    h1.header {
        display: flex;
        flex-direction: row;
        align-self: center;
        align-items: center;
        margin: 0 2em;
        padding: .1em 2em .2em;
        min-height: 22px;
        color: #f6d70a;
        font-size: 0.9em;
        letter-spacing: .03em;
        font-weight: 400;
        font-variant: small-caps;
        text-shadow: 0 0 4px black, 0 0 4px black;
        background: #2d2f89;
        & img { padding-right: 4px; }

        @include corner-bezel(0, 0, 22px, 22px );
    }

    p {
        margin: 0.6em 0;
        font-size: 0.85em;
        line-height: 1.5em;
    }

    .modal-close {
        cursor: pointer;
        position: absolute;
        right: 0;
        top: 0;
        margin: .3em;
        padding: .2em;
        border-radius: 3px;
        transition: all 0.15s;

        &:hover, &:focus, &:active { background-color: #17448E; }
    }
}
</style>
