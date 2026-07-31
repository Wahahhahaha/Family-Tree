<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import { 
    Trash2, RotateCcw, User, 
    Calendar, ShieldAlert, Ghost,
    Info, ChevronRight, Loader2,
    History, AlertCircle, Trash,
    Users, Share2, Database, Shield
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { useAlert } from '@/Composables/useAlert';

const props = defineProps({
    mergedItems: Array, // Contains both family members and staff users
    deletedSocialMedia: Array,
    translations: Object,
});

defineOptions({ layout: Layout });

const activeTab = ref('main'); // 'main' for accounts/members, 'social' for master data
const isProcessing = ref(null);
const { showConfirm } = useAlert();

const currentData = computed(() => {
    return activeTab.value === 'main' ? props.mergedItems : props.deletedSocialMedia;
});

const handleRestore = (item) => {
    isProcessing.value = item.id;
    router.post(route('recycle-bin.restore', item.id), { type: item.type }, {
        preserveScroll: true,
        onFinish: () => isProcessing.value = null,
    });
};

const handlePermanentDelete = async (item) => {
    const label = item.type === 'member' ? props.translations.member_type : (item.type === 'user' ? props.translations.user_type : props.translations.social_type);
    const name = item.type === 'user' ? item.username : item.name;

    if (await showConfirm(props.translations.delete_confirm.replace(':label', label).replace(':name', name), props.translations.delete_confirm_title, 'error')) {
        isProcessing.value = item.id;
        router.delete(route('recycle-bin.permanent', item.id), {
            data: { type: item.type },
            preserveScroll: true,
            onFinish: () => isProcessing.value = null,
        });
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.title" />

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-black text-slate-900 flex items-center gap-3 mb-4">
                    <div class="p-2.5 bg-sky-100 text-sky-600 rounded-2xl">
                        <Trash2 :size="32" />
                    </div>
                    {{ translations.title }}
                </h1>
                <p class="text-slate-500 font-medium leading-relaxed">
                    {{ translations.desc }}
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="px-6 py-4 bg-amber-50 border border-amber-100 rounded-2xl flex items-center gap-3 shadow-sm">
                    <ShieldAlert :size="16" class="text-amber-600" />
                    <span class="text-[10px] font-black text-amber-700 uppercase tracking-widest">{{ translations.purgatory_active }}</span>
                </div>
            </div>
        </div>

        <!-- Tab Switcher -->
        <div class="flex justify-center mb-12">
            <div class="bg-white p-1.5 rounded-[1.5rem] border border-slate-100 shadow-sm flex items-center gap-1">
                <button
                    @click="activeTab = 'main'"
                    class="flex items-center gap-2 px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                    :class="activeTab === 'main' ? 'bg-sky-600 text-white shadow-lg shadow-sky-100' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'"
                >
                    <Users :size="16" /> {{ translations.ancestry_accounts.replace(':count', mergedItems.length) }}
                </button>
                <button
                    @click="activeTab = 'social'"
                    class="flex items-center gap-2 px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                    :class="activeTab === 'social' ? 'bg-sky-600 text-white shadow-lg shadow-sky-100' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'"
                >
                    <Share2 :size="16" /> {{ translations.social_master.replace(':count', deletedSocialMedia.length) }}
                </button>
            </div>
        </div>

        <!-- Recycle List -->
        <div v-if="currentData.length" class="space-y-6 animate-in fade-in slide-in-from-bottom-8 duration-700">
            <div v-for="item in currentData" :key="item.id"
                class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:border-sky-100 transition-all duration-500 overflow-hidden"
                :class="{ 'opacity-80 grayscale-[0.5]': item.deleted_by_cascade_from }"
            >
                <div class="p-8 md:p-10 flex flex-col md:flex-row md:items-center gap-8">
                    <!-- Identity -->
                    <div class="flex items-center gap-6 flex-1">
                        <div v-if="item.type === 'member'" class="w-20 h-20 rounded-[1.5rem] overflow-hidden bg-slate-50 border-4 border-white shadow-lg grayscale">
                            <img v-if="item.picture" :src="item.picture" class="w-full h-full object-cover">    
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-300">  
                                <User :size="32" />
                            </div>
                        </div>
                        <div v-else class="w-20 h-20 rounded-[1.5rem] bg-slate-50 border-4 border-white shadow-lg flex items-center justify-center text-slate-300">
                            <component :is="item.type === 'user' ? Shield : Share2" :size="32" />
                        </div>

                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">
                                    {{ item.type === 'user' ? item.username : item.name }}
                                </h3>
                                <span v-if="item.deleted_by_cascade_from" class="px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded-md text-[8px] font-black uppercase tracking-widest">
                                    {{ translations.cascaded_dependent }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                <span class="flex items-center gap-1.5 text-rose-400">
                                    <Trash :size="12" /> {{ translations.deleted_on.replace(':date', formatDate(item.deleted_at)) }}
                                </span>
                                <div class="w-1.5 h-1.5 bg-slate-200 rounded-full"></div>
                                <span class="flex items-center gap-1.5">
                                    <Info :size="12" /> {{ translations.type_label.replace(':type', item.type === 'member' ? translations.member_type : (item.type === 'user' ? translations.user_type : translations.social_type)) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 shrink-0">
                        <!-- Restore Button -->
                        <div class="relative group">
                            <button
                                @click="handleRestore(item)"
                                :disabled="isProcessing !== null || (item.deleted_by_cascade_from !== undefined && item.deleted_by_cascade_from !== null)"
                                class="flex-1 md:flex-none px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest border transition-all flex items-center justify-center gap-2 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed"
                                :class="(item.deleted_by_cascade_from !== undefined && item.deleted_by_cascade_from !== null)
                                    ? 'bg-slate-50 text-slate-400 border-slate-100'
                                    : 'bg-emerald-50 text-emerald-600 border-emerald-100 hover:bg-emerald-600 hover:text-white'"
                            >
                                <Loader2 v-if="isProcessing === item.id" :size="16" class="animate-spin" />     
                                <RotateCcw v-else :size="16" /> {{ translations.restore }}
                            </button>
                            <!-- Tooltip for cascaded members -->
                            <div v-if="item.deleted_by_cascade_from !== undefined && item.deleted_by_cascade_from !== null" class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-slate-800 text-white text-[8px] font-black uppercase tracking-widest rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                                {{ translations.restore_cascade_hint }}
                            </div>
                        </div>

                        <!-- Erase Button -->
                        <button
                            @click="handlePermanentDelete(item)"
                            :disabled="isProcessing !== null"
                            class="flex-1 md:flex-none px-8 py-4 bg-rose-50 text-rose-600 rounded-2xl font-black text-xs uppercase tracking-widest border border-rose-100 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50"
                        >
                            <Trash2 :size="16" /> {{ translations.erase }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="py-32 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">        
            <div class="w-24 h-24 bg-sky-50 rounded-[2.5rem] flex items-center justify-center text-sky-200 mx-auto mb-6 shadow-inner">
                <Ghost :size="48" />
            </div>
            <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight">{{ translations.empty_title }}</h3> 
            <p class="text-slate-400 max-w-sm mx-auto font-medium">{{ translations.empty_desc }}</p>
        </div>

        <!-- Safety Notice -->
        <div class="mt-12 bg-white rounded-[2.5rem] p-10 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-48 h-48 bg-sky-500/5 blur-3xl rounded-full"></div>
            <div class="relative z-10 flex items-start gap-6">
                <div class="p-4 bg-rose-50 rounded-2xl text-rose-500 shadow-sm">
                    <ShieldAlert :size="32" />
                </div>
                <div>
                    <h4 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-2">{{ translations.protocol_title }}</h4>
                    <p class="text-slate-500 text-sm font-medium leading-relaxed max-w-2xl">
                        {{ translations.protocol_desc }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}
</style>
