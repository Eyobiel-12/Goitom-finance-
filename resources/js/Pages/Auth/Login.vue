<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    csrf_token: {
        type: String,
    },
});

const step = ref('email'); // 'email' or 'otp'
const email = ref('');
const otpSent = ref(false);
const countdown = ref(0);
const resendDisabled = ref(false);

const form = useForm({
    email: '',
    code: '',
    remember: false,
});

const otpForm = useForm({
    email: '',
    code: '',
});

const submitEmail = async () => {
    try {
        // Get CSRF token from multiple sources
        let csrfToken = props.csrf_token || '';
        
        if (!csrfToken) {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                csrfToken = csrfMeta.getAttribute('content') || '';
            }
        }
        
        if (!csrfToken && window.Laravel) {
            csrfToken = window.Laravel.csrfToken || '';
        }
        
        console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found');
        
        // Create FormData instead of JSON
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('email', form.email);
        
        const response = await fetch('/otp/send-login', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Response data:', data);

        if (data.success) {
            email.value = form.email;
            step.value = 'otp';
            otpSent.value = true;
            startCountdown();
        } else {
            form.setError('email', data.message);
        }
    } catch (error) {
        console.error('OTP Send Error:', error);
        form.setError('email', 'Er is een fout opgetreden. Probeer het opnieuw.');
    }
};

const submitOtp = async () => {
    otpForm.email = email.value;
    
    try {
        // Get CSRF token from multiple sources
        let csrfToken = props.csrf_token || '';
        
        if (!csrfToken) {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                csrfToken = csrfMeta.getAttribute('content') || '';
            }
        }
        
        if (!csrfToken && window.Laravel) {
            csrfToken = window.Laravel.csrfToken || '';
        }
        
        console.log('OTP Verify - CSRF Token:', csrfToken ? 'Found' : 'Not found');
        console.log('OTP Verify - Token value:', csrfToken.substring(0, 10) + '...');
        
        // Create FormData instead of JSON to avoid CSRF issues
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('email', otpForm.email);
        formData.append('code', otpForm.code);
        
        const response = await fetch('/otp/verify-login', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        console.log('OTP Verify - Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('OTP Verify - Error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('OTP Verify - Response data:', data);

        if (data.success) {
            router.visit(data.redirect);
        } else {
            otpForm.setError('code', data.message);
        }
    } catch (error) {
        console.error('OTP Verify Error:', error);
        otpForm.setError('code', 'Er is een fout opgetreden. Probeer het opnieuw.');
    }
};

const resendOtp = async () => {
    if (resendDisabled.value) return;

    try {
        // Get CSRF token from multiple sources
        let csrfToken = props.csrf_token || '';
        
        if (!csrfToken) {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                csrfToken = csrfMeta.getAttribute('content') || '';
            }
        }
        
        if (!csrfToken && window.Laravel) {
            csrfToken = window.Laravel.csrfToken || '';
        }
        
        // Create FormData instead of JSON
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('email', email.value);
        formData.append('type', 'login');
        
        const response = await fetch('/otp/resend', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            startCountdown();
        } else {
            otpForm.setError('code', data.message);
        }
    } catch (error) {
        console.error('OTP Resend Error:', error);
        otpForm.setError('code', 'Er is een fout opgetreden. Probeer het opnieuw.');
    }
};

const startCountdown = () => {
    countdown.value = 60;
    resendDisabled.value = true;
    
    const timer = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            clearInterval(timer);
            resendDisabled.value = false;
        }
    }, 1000);
};

const goBack = () => {
    step.value = 'email';
    otpSent.value = false;
    otpForm.reset();
    form.clearErrors();
    otpForm.clearErrors();
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <!-- Email Step -->
        <div v-if="step === 'email'">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Inloggen</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Voer uw e-mailadres in om een verificatiecode te ontvangen
                </p>
            </div>

            <form @submit.prevent="submitEmail" class="space-y-4">
                <div>
                    <InputLabel for="email" value="E-mailadres" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="uw@email.nl"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Onthoud mij</span>
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-medium text-brand-600 hover:text-brand-700"
                    >
                        Wachtwoord vergeten?
                    </Link>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Verificatiecode versturen
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- OTP Step -->
        <div v-if="step === 'otp'">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Verificatiecode</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    We hebben een 6-cijferige code verzonden naar<br>
                    <strong class="text-brand-600">{{ email }}</strong>
                </p>
            </div>

            <form @submit.prevent="submitOtp" class="space-y-4">
                <div>
                    <InputLabel for="code" value="Verificatiecode" />
                    <TextInput
                        id="code"
                        type="text"
                        class="mt-1 block w-full text-center text-2xl tracking-widest"
                        v-model="otpForm.code"
                        required
                        autofocus
                        maxlength="6"
                        placeholder="123456"
                        @input="otpForm.code = otpForm.code.replace(/[^0-9]/g, '')"
                    />
                    <InputError class="mt-2" :message="otpForm.errors.code" />
                </div>

                <div class="text-center">
                    <button
                        type="button"
                        @click="resendOtp"
                        :disabled="resendDisabled"
                        class="text-sm font-medium text-brand-600 hover:text-brand-700 disabled:text-gray-400 disabled:cursor-not-allowed"
                    >
                        <span v-if="resendDisabled">Opnieuw versturen over {{ countdown }}s</span>
                        <span v-else>Code opnieuw versturen</span>
                    </button>
                </div>

                <div class="flex items-center justify-between gap-3 pt-2">
                    <button
                        type="button"
                        @click="goBack"
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        ← Terug
                    </button>
                    
                    <PrimaryButton
                        :class="{ 'opacity-25': otpForm.processing }"
                        :disabled="otpForm.processing || otpForm.code.length !== 6"
                    >
                        Inloggen
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Nog geen account?
                <Link :href="route('register')" class="font-medium text-brand-600 hover:text-brand-700">
                    Registreer hier
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>