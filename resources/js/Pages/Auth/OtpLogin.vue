<script setup>
import { ref, watch, computed } from 'vue'
import { useForm, usePage, Head, Link } from '@inertiajs/vue3'
import Footer from '@/Components/ui/Footer.vue'
import LanguageToggle from '@/Components/LanguageToggle.vue'
import Modal from '@/Components/ui/Modal.vue'
import { AlertCircle } from 'lucide-vue-next'

defineOptions({ layout: null })

const props = defineProps({
    translations: Object,
})

const page = usePage()
const showErrorModal = ref(false)
const showSuccessModal = ref(false)

const systemname = computed(() => page.props.systemname || 'Family Tree')
const systemlogo = computed(() => page.props.systemlogo)

const form = useForm({
    email: '',
    otp: '',
    step: 'email'
})

// Watch for specific backend error key to show modal
watch(() => form.errors.email_not_found, (val) => {
    if (val) {
        showErrorModal.value = true;
    }
})

const sendOtp = () => {
    form.clearErrors();
    showSuccessModal.value = false;
    
    form.post('/login/otp/send', {
        preserveScroll: true,
        onSuccess: (pageResponse) => {
            // Check flash data from the response directly
            if (pageResponse.props.flash.otp_sent) {
                showSuccessModal.value = true;
                form.step = 'otp';
                if (pageResponse.props.flash.email) {
                    form.email = pageResponse.props.flash.email;
                }
            }
        },
        onError: (errors) => {
            console.error('OTP Send Error:', errors);
        }
    })
}

const verifyOtp = () => {
    console.log('Verifying OTP:', form.otp);
    form.post('/login/otp/verify', {
        preserveScroll: true,
        onError: (errors) => {
            console.error('OTP Verify Error:', errors);
        }
    })
}
</script>

<template>
    <Head :title="translations.otp_login_title" />

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
                            <p>{{ translations.otp_login_title }}</p>
                            <h1>{{ systemname || translations.family_tree }}</h1>
                        </div>
                    </div>

                    <div v-if="page.props.flash.success || page.props.flash.status" class="mb-6 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium text-center">
                        {{ page.props.flash.success || page.props.flash.status }}
                    </div>

                    <div v-if="form.step === 'email'">
                        <form @submit.prevent="sendOtp" class="login-form space-y-4">
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
                                    :disabled="form.processing"
                                >
                                <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="btn-login w-full flex items-center justify-center"
                            >
                                <span v-if="form.processing" class="mr-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                {{ form.processing ? 'Sending...' : translations.send_otp }}
                            </button>
                        </form>
                        
                        <div class="mt-6 text-center">
                            <Link :href="route('login')" class="text-sm text-sky-600 font-medium">
                                Back to Regular Login
                            </Link>
                        </div>
                    </div>

                    <div v-else>
                        <form @submit.prevent="verifyOtp" class="login-form space-y-4">
                            <div class="text-center text-sm text-slate-600 mb-4">
                                OTP sent to <strong>{{ form.email }}</strong>
                            </div>
                            
                            <div class="login-field">
                                <label for="otp">OTP Code</label>
                                <input
                                    v-model="form.otp"
                                    type="text"
                                    id="otp"
                                    class="login-input text-center tracking-widest text-xl font-bold"
                                    :placeholder="translations.enter_otp"
                                    required
                                    autofocus
                                    maxlength="6"
                                    :disabled="form.processing"
                                >
                                <div v-if="form.errors.otp" class="mt-2 text-sm text-red-600 text-center">
                                    {{ form.errors.otp }}
                                </div>
                            </div>

                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="btn-login w-full flex items-center justify-center"
                            >
                                <span v-if="form.processing" class="mr-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                {{ form.processing ? 'Verifying...' : translations.verify_otp }}
                            </button>
                            
                            <button 
                                type="button"
                                @click="form.step = 'email'" 
                                class="w-full text-sky-600 text-sm font-medium mt-2"
                            >
                                Change Email
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <Footer :systemname="systemname" />

        <!-- Success Modal -->
        <Modal :show="showSuccessModal" @close="showSuccessModal = false" maxWidth="md">
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">OTP Sent!</h3>
                <p class="text-slate-600 mb-8 leading-relaxed">
                    We have sent a 6-digit verification code to your email. Please check your inbox.
                </p>
                <button 
                    @click="showSuccessModal = false"
                    class="w-full py-4 bg-green-500 text-white rounded-2xl font-bold hover:bg-green-600 transition-all shadow-lg"
                >
                    Continue to Verify
                </button>
            </div>
        </Modal>

        <!-- Error Modal for Unregistered Email -->
        <Modal :show="showErrorModal" @close="showErrorModal = false" maxWidth="md">
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <AlertCircle :size="40" class="text-red-500" />
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Email Not Registered</h3>
                <p class="text-slate-600 mb-8 leading-relaxed">
                    {{ form.errors.email_not_found }}
                </p>
                <button 
                    @click="showErrorModal = false"
                    class="w-full py-4 bg-slate-900 text-white rounded-2xl font-bold hover:bg-slate-800 transition-all shadow-lg"
                >
                    Understand
                </button>
            </div>
        </Modal>
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
    margin-bottom: 20px;
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
.btn-login:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
</style>
