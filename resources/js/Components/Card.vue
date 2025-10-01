<template>
  <div :class="cardClasses">
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  hover: { type: Boolean, default: true },
  padding: { type: String, default: 'p-6' },
});

const cardClasses = computed(() => [
  'rounded-xl border border-gray-100 bg-white shadow-card transition-shadow',
  props.padding,
  props.hover ? 'hover:shadow-cardStrong' : '',
]);
</script>
