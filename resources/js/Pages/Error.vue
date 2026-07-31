<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    AlertTriangle, Ghost, ShieldAlert, 
    Home, RefreshCcw,
    Clock, Lock, Server
} from 'lucide-vue-next';
import Footer from '@/Components/ui/Footer.vue';

const props = defineProps({
    status: Number,
});

const page = usePage();
const systemname = computed(() => page.props.systemname || 'Family Trees');

defineOptions({ layout: null });

const errorData = computed(() => {
    return {
        404: {
            title: 'Lost from the Lineage',
            description: 'Sorry, the trail you are looking for cannot be found within our family archives.',
            icon: Ghost,
            color: 'text-sky-500',
            bg: 'bg-sky-50',
            border: 'border-sky-100',
        },
        403: {
            title: 'Access Restricted',
            description: 'Only certain members have permission to view the secrets behind this door.',
            icon: Lock,
            color: 'text-amber-600',
            bg: 'bg-amber-50',
            border: 'border-amber-100',
        },
        419: {
            title: 'Session Expired',
            description: 'Security is our priority. Your session has ended, please refresh the page.',
            icon: Clock,
            color: 'text-indigo-600',
            bg: 'bg-indigo-50',
            border: 'border-indigo-100',
        },
        500: {
            title: 'Internal System Error',
            description: 'There is a disturbance in our server engine. Our technical team is working to restore it.',
            icon: Server,
            color: 'text-rose-600',
            bg: 'bg-rose-50',
            border: 'border-rose-100',
        },
        503: {
            title: 'Service Under Maintenance',
            description: 'We are tidying up the archives for your convenience. Please return in a few moments.',
            icon: ShieldAlert,
            color: 'text-slate-600',
            bg: 'bg-slate-50',
            border: 'border-slate-100',
        },
    }[props.status] || {
        title: 'Unexpected Error',
        description: 'Something strange is happening. We apologize for this inconvenience.',
        icon: AlertTriangle,
        color: 'text-slate-600',
        bg: 'bg-slate-50',
        border: 'border-slate-100',
    };
});
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] font-sora flex flex-col relative overflow-hidden">
        <Head :title="`${status}: ${errorData.title}`" />

        <!-- Abstract Decorations -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/5 blur-[100px] rounded-full"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-sky-500/5 blur-[100px] rounded-full"></div>

        <div class="flex-1 flex items-center justify-center px-6 py-24">
            <div class="max-w-xl w-full text-center relative z-10">
                <!-- Icon with Animated Background -->
                <div class="relative inline-block mb-12">
                    <div class="absolute inset-0 bg-white blur-2xl rounded-full scale-150"></div>
                    <div class="relative w-32 h-32 mx-auto rounded-[2.5rem] flex items-center justify-center shadow-2xl transition-transform hover:scale-110 duration-500"
                        :class="[errorData.bg, errorData.border, 'border-2']"
                    >
                        <component :is="errorData.icon" :size="48" :class="errorData.color" />
                    </div>
                    
                    <!-- Status Code Badge -->
                    <div class="absolute -bottom-2 -right-2 px-4 py-1.5 bg-slate-900 text-white rounded-full text-[10px] font-black tracking-widest shadow-xl">
                        CODE: {{ status }}
                    </div>
                </div>

                <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-6 uppercase tracking-tight leading-tight">
                    {{ errorData.title }}
                </h1>
                
                <p class="text-slate-500 text-lg font-medium mb-12 leading-relaxed">
                    {{ errorData.description }}
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link :href="route('home')" class="w-full sm:w-auto px-10 py-5 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-2xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <Home :size="16" /> Return Home
                    </Link>
                    
                <button @click="() => window.location.reload()" class="w-full sm:w-auto px-10 py-5 bg-white border-2 border-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                    <RefreshCcw :size="16" /> Reload Page
                </button>
                </div>

                <!-- Footer Note -->
                <p class="mt-16 text-[9px] font-black text-slate-300 uppercase tracking-[0.4em] italic">
                    Family Heritage Security Protocol
                </p>
            </div>
        </div>

        <!-- Footer -->
        <Footer :systemname="systemname" />
    </div>
</template>

<style scoped>
@reference "../../css/app.css";
</style>
