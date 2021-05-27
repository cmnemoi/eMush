<template>
  <div>
    <tippy>
		<button v-tippy="{ theme: 'mush', arrow: true, appendTo :'parent'}">
			<slot name='tooltipname'></slot>
		</button>
		<template #content>
			<h1><slot name='tooltipcontenth1'></slot></h1>
			<p><slot name='tooltipcontentp'></slot></p>
		</template>
	</tippy>
  </div>
</template>

<script>
  export default {
	name: "Tooltip",
  }
</script>