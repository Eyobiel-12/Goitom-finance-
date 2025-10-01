<script setup>
import { ref, onMounted } from 'vue';

const theme = ref('system');

function applyTheme(current) {
    const root = document.documentElement;
    if (current === 'dark') {
        root.classList.add('dark');
    } else if (current === 'light') {
        root.classList.remove('dark');
    } else {
        // system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.classList.toggle('dark', prefersDark);
    }
}

function setTheme(next) {
    theme.value = next;
    localStorage.setItem('theme', next);
    applyTheme(next);
}

onMounted(() => {
    const saved = localStorage.getItem('theme');
    theme.value = saved || 'system';
    applyTheme(theme.value);
    // react to system change when in system mode
    const media = window.matchMedia('(prefers-color-scheme: dark)');
    media.addEventListener?.('change', () => {
        if (theme.value === 'system') applyTheme('system');
    });
});
</script>

<template>
    <div class="inline-flex items-center rounded-md border border-gray-200 bg-white p-0.5 dark:border-neutral-800 dark:bg-neutral-900">
        <button
            class="px-2 py-1 text-xs font-medium rounded-sm"
            :class="theme === 'light' ? 'bg-gray-100 text-gray-900 dark:bg-neutral-800 dark:text-white' : 'text-gray-600 dark:text-neutral-300'"
            @click="setTheme('light')"
        >Light</button>
        <button
            class="px-2 py-1 text-xs font-medium rounded-sm"
            :class="theme === 'system' ? 'bg-gray-100 text-gray-900 dark:bg-neutral-800 dark:text-white' : 'text-gray-600 dark:text-neutral-300'"
            @click="setTheme('system')"
        >Auto</button>
        <button
            class="px-2 py-1 text-xs font-medium rounded-sm"
            :class="theme === 'dark' ? 'bg-gray-100 text-gray-900 dark:bg-neutral-800 dark:text-white' : 'text-gray-600 dark:text-neutral-300'"
            @click="setTheme('dark')"
        >Dark</button>
    </div>
    
</template>


