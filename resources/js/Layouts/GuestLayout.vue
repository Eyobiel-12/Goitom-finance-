<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import Container from '@/Components/Container.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-red-50 via-white to-red-50 dark:from-neutral-950 dark:via-neutral-950 dark:to-neutral-900">
        <Header>
            <template #logo>
                <Link href="/" class="flex items-center gap-2">
                    <img src="/logo.png" alt="Logo" class="h-6 w-auto sm:h-7 md:h-8" />
                </Link>
            </template>
            <template #actions>
                <ThemeToggle />
            </template>
        </Header>

        <Container>
            <div class="flex min-h-[70vh] items-center justify-center py-12">
                <div class="w-full max-w-md rounded-xl border border-gray-100 bg-white p-6 shadow-card sm:p-8 dark:border-neutral-800 dark:bg-neutral-900">
                    <slot />
                </div>
            </div>
        </Container>
    </div>
</template>
