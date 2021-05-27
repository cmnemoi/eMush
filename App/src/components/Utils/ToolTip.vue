<template>
  <tippy>
    <button>Il rest X fuel</button>

    <template #content>
      Le fuel sers a bla bla bla
    </template>
  </tippy>
</template>

<script>
  import { Tippy } from 'vue-tippy'
  

  export default {
    components: {Tippy},
	name: "Tooltip",
  }
</script>

<!--<style lang="scss" scoped>

.tooltip { // container to hover to call the tooltip
  position: relative;
  display: inline-block;
  border-bottom: 0px dotted transparent; // If you want dots under the hoverable text

	&:hover .tooltip-content, &:hover ~ .tooltip-content {
		visibility: visible;
	}
}

.tooltip-content {
	visibility: hidden;

	position: absolute;
	pointer-events: none;
	z-index: 10;
	padding: 1em;
	max-width: 250px;
	width: max-content;
	background-color: rgba(35, 37, 100, .9);
	color: #fff;
	font-size : .75em;
	line-height: 1.5em;
	box-shadow: inset 0 0 12px 3px #3965fb, inset 0 0 0 2px #3965fb;

	&::after { // Adds the triangles in the corners
		content: "";
		position: absolute;
		left: 0;
		right: 0;
		top: 0;
		bottom: 0;
		margin: 3px;
		border: 5px solid transparent;
		border-image: url('~@/assets/images/ToolTip-corners.gif') 50% round;
	}

	p { margin: 0; }

	b, strong { color: #01c3df !important; }

	h1 {
		margin: 0 0 .4em;
		padding-left: 16px;
		font-size: 1.2em;
		letter-spacing: .03em;
		font-variant: small-caps;
		background: transparent url('~@/assets/images/notes.gif') no-repeat center left;
	}
}

</style>
-->