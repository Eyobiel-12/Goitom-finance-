<template>
  <div class="container" :class="classes">
    <slot />
  </div>
</template>

<script setup>
const props = defineProps({
  classes: {
    type: [String, Array, Object],
    default: '',
  },
});
</script>
