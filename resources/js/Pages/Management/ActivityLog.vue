<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import { 
    Activity, User, Clock, 
    Monitor, Globe, MapPin, 
    ChevronRight, ChevronLeft,
    Database, Info, Search
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    logs: Object,
    translations: Object,
});

defineOptions({ layout: Layout });

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getActionColor = (action) => {
    const act = action.toLowerCase();
    if (act.includes('create') || act.includes('add')) return 'bg-emerald-50 text-emerald-600 border-emerald-100';
    if (act.includes('delete') || act.includes('remove')) return 'bg-rose-50 text-rose-600 border-rose-100';    
    if (act.includes('update') || act.includes('edit')) return 'bg-amber-50 text-amber-600 border-amber-100';   
    return 'bg-sky-50 text-sky-600 border-sky-100';
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
                        <Activity :size="32" />
                    </div>
                    {{ translations.title }}
                </h1>
                <p class="text-slate-500 font-medium leading-relaxed">
                    {{ translations.desc }}
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="px-6 py-4 bg-white border border-slate-100 rounded-2xl flex items-center gap-3 shadow-sm">
                    <Database :size="16" class="text-sky-600" />
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations.total_events.replace(':count', logs.total) }}</span>
                </div>
            </div>
        </div>

        <!-- Activity Table -->
        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-8 duration-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.timestamp }}</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.initiator }}</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.action }}</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.origin }}</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.context }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/50 transition-colors group">
                            <!-- Timestamp -->
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-white rounded-xl shadow-sm text-slate-400 group-hover:text-sky-500 transition-colors">
                                        <Clock :size="14" />
                                    </div>
                                    <span class="text-xs font-bold text-slate-600">{{ formatDate(log.created_at) }}</span>
                                </div>
                            </td>

                            <!-- Initiator -->
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <User :size="14" />
                                    </div>
                                    <span class="text-sm font-black text-slate-900 uppercase tracking-tighter">{{ log.username || translations.system }}</span>
                                </div>
                            </td>

                            <!-- Action -->
                            <td class="px-8 py-6">
                                <span class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest border" :class="getActionColor(log.action)">
                                    {{ log.action }}
                                </span>
                            </td>

                            <!-- Origin (IP & Location) -->
                            <td class="px-8 py-6">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500">
                                        <Globe :size="12" /> {{ log.ip_adress }}
                                    </div>
                                    <div v-if="log.latitude" class="flex items-center gap-1.5 text-[9px] text-slate-400">
                                        <MapPin :size="10" /> {{ log.latitude }}, {{ log.longitude }}
                                    </div>
                                </div>
                            </td>

                            <!-- Context -->
                            <td class="px-8 py-6 max-w-xs">
                                <div class="flex items-center gap-2 text-[10px] text-slate-400 italic">
                                    <Info :size="12" class="shrink-0" />
                                    <p class="truncate">{{ log.context || translations.no_details }}</p>       
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State Row -->
                        <tr v-if="!logs.data.length">
                            <td colspan="5" class="py-24 text-center">
                                <Activity :size="48" class="text-slate-100 mx-auto mb-4" />
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">{{ translations.empty_chronicle }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.links.length > 3" class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    {{ translations.showing_events.replace(':from', logs.from || 0).replace(':to', logs.to || 0).replace(':total', logs.total || 0) }}
                </div>
                <div class="flex items-center gap-2">
                    <template v-for="(link, k) in logs.links" :key="k">
                        <Link v-if="link.url"
                            :href="link.url"
                            class="w-8 h-8 flex items-center justify-center rounded-xl text-[10px] font-black transition-all"
                            :class="link.active ? 'bg-sky-600 text-white shadow-lg shadow-sky-100' : 'bg-white text-slate-400 hover:bg-sky-50 hover:text-sky-600 border border-slate-100'"
                            v-html="link.label.includes('Previous') ? '&laquo;' : (link.label.includes('Next') ? '&raquo;' : link.label)"
                        />
                    </template>
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
