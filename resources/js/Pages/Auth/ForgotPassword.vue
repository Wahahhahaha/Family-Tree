<script setup>
import { computed } from 'vue'
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

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'))
}
</script>

<template>
    <Head :title="translations.forgot_password_title" />

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
                            <p>{{ translations.forgot_password_title }}</p>
                            <h1>{{ systemname || translations.family_tree }}</h1>
                        </div>
                    </div>

                    <div class="mb-6 text-sm text-gray-600">
                        {{ translations.forgot_password_desc }}
                    </div>

                    <div v-if="page.props.flash.status" class="mb-4 font-medium text-sm text-green-600">        
                        {{ page.props.flash.status }}
                    </div>

                    <form @submit.prevent="submit" class="login-form">
                        <div class="login-field">
                            <label for="email">{{ translations.email_address }}</label>
                            <input
                                v-model="form.email"
                                type="email"
                                id="email"
                                class="login-input"
                                :placeholder="translations.enter_email"
                                required
                                autofocus
                            >
                            <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-8">
                            <Link
                                :href="route('login')"
                                class="text-sm text-gray-600 hover:text-gray-900 font-medium"
                            >
                                {{ translations.back_to_login }}
                            </Link>

                            <button
                                type="submit"
                                class="btn-login w-auto px-8"
                                :disabled="form.processing"
                            >
                                {{ translations.send_reset_link }}
                            </button>
                        </div>
                    </form>
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
    margin-bottom: 24px;
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
.login-brand-initial {
    font-size: 1.5rem;
    font-weight: 800;
    color: #94a3b8;
}
.login-brand-copy {
    flex: 1;
    min-width: 0;
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
    white-space: normal;
    word-break: break-word;
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
    border-color: #0ea5e9;
}
.btn-login {
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
</style>
