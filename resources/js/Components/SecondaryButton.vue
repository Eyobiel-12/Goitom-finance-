<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
  <button
    class="inline-flex items-center rounded-lg border border-brand-500 bg-transparent px-4 py-2 text-sm font-semibold text-brand-600 transition duration-150 ease-in-out hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:ring-offset-2 active:bg-brand-100"
  >
    <slot />
  </button>
</template>
