<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, Head, Link } from '@inertiajs/vue3'
import Footer from '@/Components/ui/Footer.vue'
import LanguageToggle from '@/Components/LanguageToggle.vue'

defineOptions({ layout: null })

defineProps({
    translations: Object,
})

const page = usePage()
const systemname = computed(() => page.props.systemname)
const systemlogo = computed(() => page.props.systemlogo)

const showPassword = ref(false)
const showOtp = ref(false)

const form = useForm({
    username: '',
    password: '',
})

const otpForm = useForm({
    email: '',
})

const submit = () => form.post('/login')
const submitOtp = () => otpForm.post('/login/otp/send')
</script>

<template>
    <Head :title="translations.title" />

    <!-- Floating Language Toggle -->
    <div class="fixed top-6 right-6 z-50">
        <LanguageToggle />
    </div>

    <div class="min-h-screen flex flex-col bg-[#f4f8fb]">
        <div class="page-login flex-1">
            <div class="login-shell">
                <div class="login-card-body">
                    <div class="login-brand">
                        <div class="login-brand-logo">
                            <img v-if="systemlogo" :src="systemlogo" :alt="systemname">
                            <span v-else class="login-brand-initial">{{ systemname?.charAt(0) || 'F' }}</span>
                        </div>
                        <div class="login-brand-copy">
                            <p>{{ translations.welcome_to }}</p>
                            <h1>{{ systemname || translations.family_tree }}</h1>
                        </div>
                    </div>

                    <div v-if="!showOtp">
                        <form @submit.prevent="submit" class="login-form">
                            <div v-if="form.errors.username" class="mb-4 p-3 bg-rose-50 border border-rose-100 rounded-xl text-rose-600 text-xs font-bold uppercase tracking-widest text-center">
                                {{ form.errors.username }}
                            </div>
                            <div class="login-field">
                                <label for="username">{{ translations.username }}</label>
                                <input v-model="form.username" type="text" id="username" class="login-input" :class="{ 'border-rose-300 bg-rose-50/30': form.errors.username }" :placeholder="translations.placeholder_username" required autofocus>
                            </div>
                            <div class="login-field">
                                <label for="password">{{ translations.password }}</label>
                                <div class="relative">
                                    <input v-model="form.password" :type="showPassword ? 'text' : 'password'" id="password" class="login-input" :class="{ 'border-rose-300 bg-rose-50/30': form.errors.username }" :placeholder="translations.placeholder_password" required>
                                    <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L5.136 5.136m13.728 13.728L13.878 13.878" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="forgot-password">
                                <Link :href="route('password.request')">{{ translations.forgot_password }}</Link>
                            </div>
                            <button type="submit" class="btn-login" :disabled="form.processing">{{ translations.sign_in_button }}</button>
                        </form>
                    </div>

                    <div v-else>
                        <form @submit.prevent="submitOtp" class="login-form">
                            <div class="login-field">
                                <label for="email">{{ translations.email_address }}</label>
                                <input v-model="otpForm.email" type="email" id="email" class="login-input" placeholder="name@example.com" required>
                            </div>
                            <button type="submit" class="btn-login" :disabled="otpForm.processing">{{ translations.send_otp_button }}</button>
                        </form>
                    </div>

                    <div class="social-divider">
                        <span>{{ translations.or_divider }}</span>
                    </div>

                    <div class="social-auth-row">
                        <button type="button" @click="showOtp = !showOtp" class="btn-secondary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!showOtp" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>{{ showOtp ? translations.username_login : translations.otp_login }}</span>
                        </button>
                        <a :href="route('login.google')" class="btn-secondary">
                            <svg viewBox="0 0 24 24" class="w-5 h-5">
                                <path fill="#4285F4" d="M21.58 12.24c0-.76-.07-1.49-.19-2.2H12v4.16h5.38a4.61 4.61 0 0 1-2 3.03v2.52h3.23c1.89-1.74 2.97-4.31 2.97-7.51z"></path>
                                <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.61-2.45l-3.23-2.52c-.9.6-2.06.95-3.38.95-2.6 0-4.8-1.76-5.58-4.12H3.08v2.6A9.99 9.99 0 0 0 12 22z"></path>
                                <path fill="#FBBC05" d="M6.42 13.86A5.98 5.98 0 0 1 6.1 12c0-.64.11-1.27.32-1.86v-2.6H3.08A9.99 9.99 0 0 0 2 12c0 1.61.39 3.14 1.08 4.46l3.34-2.6z"></path>
                                <path fill="#EA4335" d="M12 6.03c1.47 0 2.78.5 3.82 1.5l2.86-2.86C16.95 3.06 14.69 2 12 2A9.99 9.99 0 0 0 3.08 7.54l3.34 2.6C7.2 7.79 9.4 6.03 12 6.03z"></path>
                            </svg>
                            <span>{{ translations.google_login }}</span>
                        </a>
                    </div>
                    
                    <div class="login-footer">
                        {{ translations.footer_contact }}
                    </div>
                </div>
            </div>
        </div>

        <Footer :systemname="systemname" />
    </div>
</template>

<style scoped>
.page-login {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: 
        radial-gradient(circle at 6% 10%, #dff2fb 0%, transparent 36%),
        radial-gradient(circle at 92% 88%, #dcf5eb 0%, transparent 36%),
        #f4f8fb;
}
.login-shell {
    width: 100%;
    max-width: 480px;
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}
.login-brand {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 32px;
}
.login-brand-logo {
    width: 64px;
    height: 64px;
    flex-shrink: 0;
    background: #f8fafc;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}
.login-brand-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.login-brand-placeholder {
    font-size: 1.5rem;
    font-weight: 800;
    color: #94a3b8;
}
.login-brand-copy {
    overflow: hidden;
    flex: 1;
}
.login-brand-copy p {
    margin: 0;
    font-size: 0.7rem;
    color: #64748b;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}
.login-brand-copy h1 {
    margin: 0;
    font-size: 1.25rem;
    color: #0f172a;
    font-weight: 800;
    line-height: 1.2;
}
.truncate-2-lines {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.login-field {
    margin-bottom: 16px;
}
.login-field label {
    display: block;
    margin-bottom: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
}
.login-input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.2s;
}
.login-input:focus {
    border-color: #059669;
}
.forgot-password {
    text-align: left;
    margin-bottom: 24px;
}
.forgot-password a {
    color: #0284c7;
    font-size: 0.875rem;
    font-weight: 500;
}
.btn-login {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
}
.btn-login:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 15px rgba(14, 165, 233, 0.3);
    filter: brightness(1.05);
}
.btn-login:active {
    transform: translateY(0);
}
.btn-login:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
.social-divider {
    text-align: center;
    margin: 24px 0;
    position: relative;
    color: #94a3b8;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.1em;
}
.social-divider::before, .social-divider::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 42%;
    height: 1px;
    background: #f1f5f9;
}
.social-divider::before { left: 0; }
.social-divider::after { right: 0; }
.social-auth-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.btn-secondary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: white;
    font-size: 0.875rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-secondary:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}
.login-footer {
    text-align: center;
    margin-top: 24px;
    font-size: 0.875rem;
    color: #64748b;
}
</style>
