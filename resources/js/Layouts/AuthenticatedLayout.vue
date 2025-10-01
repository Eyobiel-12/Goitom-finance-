<script setup>
import { ref, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { Link } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(false);
const sidebarCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');

watch(sidebarCollapsed, (v) => localStorage.setItem('sidebarCollapsed', String(v)));

const navigationSections = [
    {
        title: 'MAIN',
        items: [
            { name: 'Dashboard', href: 'dashboard', icon: 'M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z', current: () => route().current('dashboard') },
            { name: 'Clients', href: 'clients.index', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z', current: () => route().current('clients.*') },
            { name: 'Projects', href: 'projects.index', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', current: () => route().current('projects.*') },
            { name: 'Time Tracking', href: 'time-entries.index', icon: 'M12 8v4l3 3M12 22a10 10 0 110-20 10 10 0 010 20z', current: () => route().current('time-entries.*') },
            { name: 'Settings', href: 'profile.edit', icon: 'M11 3a1 1 0 012 0v2a7 7 0 013.536 1.464l1.414-1.414a1 1 0 011.414 1.414L18.364 6.464A7 7 0 0119.828 10H22a1 1 0 010 2h-2.172a7 7 0 01-1.464 3.536l1.414 1.414a1 1 0 11-1.414 1.414l-1.414-1.414A7 7 0 0113 19.828V22a1 1 0 01-2 0v-2.172a7 7 0 01-3.536-1.464l-1.414 1.414a1 1 0 11-1.414-1.414l1.414-1.414A7 7 0 014.172 12H2a1 1 0 010-2h2.172a7 7 0 011.464-3.536L4.222 4.05A1 1 0 015.636 2.636l1.414 1.414A7 7 0 0111 5V3z', current: () => route().current('profile.*') }
        ]
    },
    {
        title: 'FINANCE',
        items: [
            { name: 'Invoices', href: 'invoices.index', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', current: () => route().current('invoices.*') },
            { name: 'Expenses', href: 'expenses.index', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1', current: () => route().current('expenses.*') },
            { name: 'Payments', href: 'payments.index', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', current: () => route().current('payments.*'), badge: null }
        ]
    }
];
</script>

<template>
    <a href="#main-content" class="skip-link">Skip to content</a>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 dark:from-neutral-950 dark:via-neutral-950 dark:to-neutral-900">
        <!-- Mobile sidebar -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-50 lg:hidden">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="sidebarOpen = false"></div>
            <div class="relative flex w-64 flex-col bg-white/90 backdrop-blur-md shadow-2xl dark:bg-neutral-900/90 dark:text-neutral-200">
                <div class="flex h-16 items-center justify-between px-4">
                    <Link :href="route('dashboard')" @click="sidebarOpen = false">
                        <img src="/logo.png" alt="Logo" class="h-7 w-auto" />
                    </Link>
                    <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600 dark:text-neutral-400 dark:hover:text-neutral-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="h-px mx-3 bg-gradient-to-r from-transparent via-gray-200 to-transparent dark:via-neutral-800"></div>
                <nav class="flex-1 px-2 py-4 space-y-6" role="navigation" aria-label="Primary">
                    <div v-for="section in navigationSections" :key="section.title" class="space-y-1">
                        <h3 class="sticky top-0 z-10 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500 backdrop-blur bg-white/70 dark:bg-neutral-900/70 dark:text-neutral-500">
                            {{ section.title }}
                        </h3>
                        <div class="space-y-1">
                                <Link v-for="item in section.items" :key="item.name" :href="route(item.href)" @click="sidebarOpen = false" :aria-current="item.current() ? 'page' : undefined" :class="[
                                    item.current() 
                                        ? 'relative bg-brand-600 text-white border-brand-600 shadow-cardStrong ring-1 ring-black/5 dark:ring-white/5' 
                                        : 'border-transparent text-ink-700 hover:bg-gray-100/80 hover:text-ink-900 hover:shadow-sm dark:text-neutral-300 dark:hover:bg-neutral-800/80 dark:hover:text-white',
                                    'group flex items-center px-4 py-3 text-base font-medium rounded-xl border-l-4 transition-all duration-200 focus-visible-ring hover:translate-x-0.5'
                                ]">
                                <svg class="mr-3 h-5 w-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" :class="item.current() ? 'text-white' : 'text-ink-500 dark:text-neutral-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span class="flex-1">{{ item.name }}</span>
                                <span v-if="item.badge" class="ml-2 inline-flex items-center rounded-full bg-brand-600/20 px-2 py-0.5 text-xs font-medium text-brand-700 dark:text-brand-300">{{ item.badge }}</span>
                                <svg v-if="item.current()" class="h-5 w-5 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Desktop sidebar -->
        <div :class="[
            'hidden lg:fixed lg:inset-y-0 lg:flex lg:flex-col transition-all duration-300 ease-in-out',
            sidebarCollapsed ? 'lg:w-16' : 'lg:w-64'
        ]">
            <div class="flex flex-grow flex-col overflow-y-auto bg-white/80 backdrop-blur border-r border-gray-200 dark:bg-neutral-900/80 dark:border-neutral-800 dark:text-neutral-200 bg-[radial-gradient(60%_40%_at_0%_0%,rgba(220,38,38,0.06),transparent),radial-gradient(50%_50%_at_100%_0%,rgba(2,6,23,0.05),transparent)]">
                <!-- Logo -->
                <div class="flex h-16 shrink-0 items-center px-4">
                    <Link :href="route('dashboard')" class="flex items-center">
                        <img src="/logo.png" alt="Logo" :class="sidebarCollapsed ? 'h-6 w-auto' : 'h-8 w-auto'" />
                    </Link>
                </div>
                <div class="h-px mx-3 bg-gradient-to-r from-transparent via-gray-200 to-transparent dark:via-neutral-800"></div>

                <!-- Navigation -->
                <nav class="flex-1 px-2 py-4 space-y-6" role="navigation" aria-label="Primary">
                    <div v-for="section in navigationSections" :key="section.title" class="space-y-1">
                        <h3 v-if="!sidebarCollapsed" class="sticky top-0 z-10 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-ink-500 backdrop-blur bg-white/70 dark:bg-neutral-900/70 dark:text-neutral-500">
                            {{ section.title }}
                        </h3>
                        <div class="space-y-1">
                                <Link v-for="item in section.items" :key="item.name" :href="route(item.href)" :aria-current="item.current() ? 'page' : undefined" :class="[
                                    item.current() 
                                        ? 'relative bg-brand-600 text-white border-brand-600 shadow-cardStrong ring-1 ring-black/5 dark:ring-white/5' 
                                        : 'border-transparent text-ink-700 hover:bg-gray-100/80 hover:text-ink-900 hover:shadow-sm dark:text-neutral-300 dark:hover:bg-neutral-800/80 dark:hover:text-white',
                                    'group flex items-center px-4 py-3 text-sm font-medium rounded-xl border-l-4 transition-all duration-200 focus-visible-ring',
                                    sidebarCollapsed ? 'justify-center px-3 hover:translate-x-0' : 'hover:translate-x-0.5'
                                ]" :title="sidebarCollapsed ? item.name : ''">
                                <svg class="h-5 w-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" :class="item.current() ? 'text-white' : 'text-ink-500 dark:text-neutral-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span v-if="!sidebarCollapsed" class="ml-3 flex-1">{{ item.name }}</span>
                                <span v-if="!sidebarCollapsed && item.badge" class="ml-2 inline-flex items-center rounded-full bg-brand-600/20 px-2 py-0.5 text-xs font-medium text-brand-700 dark:text-brand-300">{{ item.badge }}</span>
                                <svg v-if="!sidebarCollapsed && item.current()" class="h-5 w-5 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <span v-if="sidebarCollapsed" class="sr-only">{{ item.name }}</span>
                            </Link>
                        </div>
                    </div>
                </nav>

                        <!-- User menu -->
                        <div class="flex shrink-0 border-t border-gray-200 p-4 dark:border-neutral-800">
                            <div v-if="!sidebarCollapsed" class="group block w-full flex-shrink-0">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-sm">
                                            <span class="text-sm font-semibold text-white">{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-ink-700 group-hover:text-ink-900 dark:text-neutral-200 dark:group-hover:text-white">{{ $page.props.auth.user.name }}</p>
                                        <p class="text-xs font-medium text-ink-500 group-hover:text-ink-700 dark:text-neutral-400 dark:group-hover:text-neutral-300">{{ $page.props.auth.user.email }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 flex space-x-3">
                                    <Link :href="route('profile.edit')" class="text-xs text-ink-500 hover:text-ink-700 hover:underline dark:text-neutral-400 dark:hover:text-neutral-200 focus-visible-ring px-2 py-1 rounded-md hover:bg-gray-100/50 dark:hover:bg-neutral-800/50 transition-colors">Profiel</Link>
                                    <Link :href="route('logout')" method="post" as="button" class="text-xs text-ink-500 hover:text-ink-700 hover:underline dark:text-neutral-400 dark:hover:text-neutral-200 focus-visible-ring px-2 py-1 rounded-md hover:bg-gray-100/50 dark:hover:bg-neutral-800/50 transition-colors">Uitloggen</Link>
                                </div>
                            </div>
                            <div v-else class="flex justify-center w-full">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-sm hover:shadow-md transition-shadow">
                                    <span class="text-sm font-semibold text-white">{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
                                </div>
                            </div>
                        </div>
            </div>
        </div>

        <!-- Main content -->
        <div :class="[
            'transition-all duration-300 ease-in-out',
            sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'
        ]">
            <!-- Top bar -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-100 bg-white/70 backdrop-blur px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8 dark:border-neutral-800 dark:bg-neutral-900/70">
                <!-- Mobile menu button -->
                <button type="button" class="-m-2.5 p-2.5 text-ink-700 lg:hidden dark:text-neutral-200 focus-visible-ring" @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Desktop sidebar toggle -->
                <button type="button" class="hidden lg:block -m-2.5 p-2.5 text-ink-700 hover:text-ink-900 dark:text-neutral-300 dark:hover:text-white focus-visible-ring" @click="sidebarCollapsed = !sidebarCollapsed">
                    <span class="sr-only">Toggle sidebar</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="!sidebarCollapsed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1"></div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <ThemeToggle />
                        <LanguageSwitcher :current-language="$page.props.locale" />
                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button type="button" class="-m-1.5 flex items-center p-1.5">
                                        <span class="sr-only">Open user menu</span>
                                        <div class="h-8 w-8 rounded-full bg-brand-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <span class="hidden lg:flex lg:items-center">
                                            <span class="ml-4 text-sm font-semibold leading-6 text-ink-900 dark:text-white" aria-hidden="true">{{ $page.props.auth.user.name }}</span>
                                            <svg class="ml-2 h-5 w-5 text-ink-500 dark:text-neutral-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">Profiel</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">Uitloggen</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Heading -->
            <header class="bg-white/70 backdrop-blur shadow-sm dark:bg-neutral-900/70" v-if="$slots.header">
                <div class="px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main id="main-content" class="py-8">
                <div class="px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>