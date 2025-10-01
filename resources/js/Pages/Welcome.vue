<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import Header from '@/Components/Header.vue';
import Card from '@/Components/Card.vue';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});
</script>

<template>
    <Head title="Zemen - Financial Management for Habesha Freelancers" />
    
    <div class="min-h-screen bg-gradient-to-br from-red-50 via-white to-red-50">
        <!-- Header -->
        <Header>
            <template #logo>
                <div class="flex items-center gap-2">
                    <img src="/logo.png" alt="Zemen" class="h-7 w-7" />
                    </div>
            </template>
            <template #actions>
                <nav v-if="canLogin" class="flex items-center gap-3">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="px-3 py-2 text-ink-700 hover:text-ink-900 transition-colors">Dashboard</Link>
                        <template v-else>
                        <Link :href="route('login')" class="px-3 py-2 text-ink-700 hover:text-ink-900">login</Link>
                        <Link v-if="canRegister" :href="route('register')" class="bg-brand-500 text-white px-5 py-2 rounded-lg hover:bg-brand-600 transition-colors">Sign up</Link>
                        </template>
                    </nav>
            </template>
        </Header>

        <!-- Main Content -->
        <main class="relative z-10 pb-16">
            <Container>
                <!-- Announcement Banner -->
                <div class="text-center mb-8">
                    <div class="inline-block bg-white/80 backdrop-blur-sm border border-gray-200 rounded-full px-6 py-2 text-sm text-gray-700 hover:bg-white/90 transition-colors duration-200 cursor-pointer">
                        Announcing $50M in Managed Assets →
                            </div>
                                    </div>

                <!-- Hero Section -->
                <div class="text-center mb-16">
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-ink-900 mb-6 leading-tight">
                        <span class="block">Financial Management</span>
                        <span class="block text-brand-500">for Habesha Freelancers</span>
                    </h1>
                    
                    <p class="text-xl text-ink-600 max-w-3xl mx-auto mb-12 leading-relaxed">
                        Comprehensive financial tools designed for Ethiopian and Eritrean freelancers. 
                        Our platform combines traditional business wisdom with modern financial management strategies.
                    </p>
                    
                    <Link v-if="canRegister" :href="route('register')" class="inline-block bg-brand-500 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-brand-600 transition-colors duration-200 shadow-card hover:shadow-cardStrong transform hover:-translate-y-1">
                        Begin Your Journey →
                    </Link>
                                </div>

                <!-- Features Grid -->
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card>
                        <div class="flex items-center justify-center w-16 h-16 bg-brand-100 rounded-full mb-4">
                            <svg class="w-8 h-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        <h3 class="text-xl font-semibold text-ink-900 mb-3">Invoice Management</h3>
                        <p class="text-ink-600 text-sm leading-relaxed">Create, send, and track invoices with professional templates designed for freelancers and small businesses.</p>
                    </Card>

                    <Card>
                        <div class="flex items-center justify-center w-16 h-16 bg-brand-100 rounded-full mb-4">
                            <svg class="w-8 h-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        <h3 class="text-xl font-semibold text-ink-900 mb-3">Expense Tracking</h3>
                        <p class="text-ink-600 text-sm leading-relaxed">Track business expenses, categorize spending, and generate reports for tax purposes and financial planning.</p>
                    </Card>

                    <Card>
                        <div class="flex items-center justify-center w-16 h-16 bg-brand-100 rounded-full mb-4">
                            <svg class="w-8 h-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        <h3 class="text-xl font-semibold text-ink-900 mb-3">Client Management</h3>
                        <p class="text-ink-600 text-sm leading-relaxed">Organize client information, track project progress, and maintain professional relationships with ease.</p>
                    </Card>

                    <Card>
                        <div class="flex items-center justify-center w-16 h-16 bg-brand-100 rounded-full mb-4">
                            <svg class="w-8 h-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-ink-900 mb-3">Financial Reports</h3>
                        <p class="text-ink-600 text-sm leading-relaxed">Generate comprehensive financial reports and analytics to track your business growth and make informed decisions.</p>
                    </Card>
                    </div>
            </Container>
                </main>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-gray-200 bg-white/60 backdrop-blur">
            <Container>
                <div class="py-8 text-center">
                    <p class="text-sm text-ink-600">Zemen Financial Management - Empowering Habesha Freelancers</p>
                </div>
            </Container>
                </footer>
    </div>
</template>