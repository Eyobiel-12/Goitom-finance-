<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const step = ref('email'); // 'email', 'otp', or 'details'
const email = ref('');
const otpSent = ref(false);
const countdown = ref(0);
const resendDisabled = ref(false);

const emailForm = useForm({
    email: '',
});

const otpForm = useForm({
    email: '',
    code: '',
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// Check if user came from OTP verification
onMounted(() => {
    const verifiedEmail = document.querySelector('meta[name="verified-email"]')?.getAttribute('content');
    if (verifiedEmail) {
        email.value = verifiedEmail;
        form.email = verifiedEmail;
        step.value = 'details';
    }
});

const submitEmail = async () => {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const response = await fetch('/otp/send-registration', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ email: emailForm.email }),
        });

        const data = await response.json();

        if (data.success) {
            email.value = emailForm.email;
            step.value = 'otp';
            otpSent.value = true;
            startCountdown();
        } else {
            emailForm.setError('email', data.message);
        }
    } catch (error) {
        console.error('Registration OTP Send Error:', error);
        emailForm.setError('email', 'Er is een fout opgetreden. Probeer het opnieuw.');
    }
};

const submitOtp = async () => {
    otpForm.email = email.value;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const response = await fetch('/otp/verify-registration', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                email: otpForm.email,
                code: otpForm.code,
            }),
        });

        const data = await response.json();

        if (data.success) {
            form.email = email.value;
            step.value = 'details';
        } else {
            otpForm.setError('code', data.message);
        }
    } catch (error) {
        console.error('Registration OTP Verify Error:', error);
        otpForm.setError('code', 'Er is een fout opgetreden. Probeer het opnieuw.');
    }
};

const submitRegistration = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const resendOtp = async () => {
    if (resendDisabled.value) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const response = await fetch('/otp/resend', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                email: email.value,
                type: 'registration',
            }),
        });

        const data = await response.json();

        if (data.success) {
            startCountdown();
        } else {
            otpForm.setError('code', data.message);
        }
    } catch (error) {
        console.error('Registration OTP Resend Error:', error);
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
    if (step.value === 'otp') {
        step.value = 'email';
        otpSent.value = false;
        otpForm.reset();
        otpForm.clearErrors();
    } else if (step.value === 'details') {
        step.value = 'otp';
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Registreren" />

        <!-- Email Step -->
        <div v-if="step === 'email'">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Account aanmaken</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Voer uw e-mailadres in om te beginnen met registreren
                </p>
            </div>

            <form @submit.prevent="submitEmail" class="space-y-4">
                <div>
                    <InputLabel for="email" value="E-mailadres" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="emailForm.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="uw@email.nl"
                    />
                    <InputError class="mt-2" :message="emailForm.errors.email" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <PrimaryButton
                        :class="{ 'opacity-25': emailForm.processing }"
                        :disabled="emailForm.processing"
                    >
                        Verificatiecode versturen
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- OTP Step -->
        <div v-if="step === 'otp'">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">E-mailadres verifiëren</h2>
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
                        Verifiëren
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Registration Details Step -->
        <div v-if="step === 'details'">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Accountgegevens</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Vul uw gegevens in om uw account te voltooien<br>
                    <strong class="text-brand-600">{{ email }}</strong> is geverifieerd ✓
                </p>
            </div>

            <form @submit.prevent="submitRegistration" class="space-y-4">
                <div>
                    <InputLabel for="name" value="Volledige naam" />
                    <TextInput 
                        id="name" 
                        type="text" 
                        class="mt-1 block w-full" 
                        v-model="form.name" 
                        required 
                        autofocus 
                        autocomplete="name" 
                        placeholder="Jan de Vries"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="E-mailadres" />
                    <TextInput 
                        id="email" 
                        type="email" 
                        class="mt-1 block w-full bg-gray-100 dark:bg-gray-800" 
                        v-model="form.email" 
                        required 
                        readonly
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="Wachtwoord" />
                    <TextInput 
                        id="password" 
                        type="password" 
                        class="mt-1 block w-full" 
                        v-model="form.password" 
                        required 
                        autocomplete="new-password"
                        placeholder="Minimaal 8 karakters"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Wachtwoord bevestigen" />
                    <TextInput 
                        id="password_confirmation" 
                        type="password" 
                        class="mt-1 block w-full" 
                        v-model="form.password_confirmation" 
                        required 
                        autocomplete="new-password"
                        placeholder="Herhaal uw wachtwoord"
                    />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
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
                        :class="{ 'opacity-25': form.processing }" 
                        :disabled="form.processing"
                    >
                        Account aanmaken
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Al een account?
                <Link :href="route('login')" class="font-medium text-brand-600 hover:text-brand-700">
                    Log hier in
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>