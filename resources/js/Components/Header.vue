<template>
  <header class="relative z-20 border-b border-gray-200/60 bg-white/80 backdrop-blur">
    <div class="container flex h-16 items-center justify-between">
      <div class="flex items-center gap-2">
        <slot name="logo">
          <RouterLink :href="route('dashboard')" class="flex items-center gap-2">
            <img src="/logo.png" alt="Zemen" class="h-8 w-8" />
          </RouterLink>
        </slot>
      </div>

      <nav class="hidden gap-6 md:flex">
        <slot name="nav" />
      </nav>

      <div class="flex items-center gap-2">
        <slot name="actions" />
      </div>
    </div>
  </header>
</template>

<script setup>
import { Link as RouterLink } from '@inertiajs/vue3';
</script>
