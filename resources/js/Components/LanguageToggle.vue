<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');

defineProps({
    isMobile: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div v-if="!isMobile" class="flex items-center bg-slate-100/50 rounded-xl p-1 shrink-0 border border-slate-100">
        <Link :href="route('locale.switch', 'en')" 
            class="px-3 py-1.5 rounded-lg text-[10px] font-black transition-all uppercase tracking-wider"
            :class="currentLocale === 'en' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'">
            EN
        </Link>
        <Link :href="route('locale.switch', 'id')" 
            class="px-3 py-1.5 rounded-lg text-[10px] font-black transition-all uppercase tracking-wider"
            :class="currentLocale === 'id' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'">
            ID
        </Link>
    </div>

    <div v-else class="flex items-center bg-white rounded-lg p-1 border border-slate-100">
        <Link :href="route('locale.switch', 'en')" 
            class="px-2.5 py-1 rounded text-[9px] font-black transition-all uppercase tracking-widest"
            :class="currentLocale === 'en' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400'">
            EN
        </Link>
        <Link :href="route('locale.switch', 'id')" 
            class="px-2.5 py-1 rounded text-[9px] font-black transition-all uppercase tracking-widest"
            :class="currentLocale === 'id' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400'">
            ID
        </Link>
    </div>
</template>
