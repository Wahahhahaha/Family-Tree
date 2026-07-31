<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Download, Database, Trash2, Play, Clock, FileText, CheckCircle, Loader2 } from 'lucide-vue-next';      
import { useAlert } from '@/Composables/useAlert';

const props = defineProps({
    backups: Array,
    translations: Object,
});

const isProcessing = ref(false);
const { showConfirm } = useAlert();

const runBackup = () => {
    isProcessing.value = true;
    router.post(route('backup.run'), {}, {
        onFinish: () => {
            isProcessing.value = false;
        },
        preserveScroll: true,
    });
};

const deleteBackup = async (filename) => {
    if (await showConfirm(props.translations.delete_confirm.replace(':filename', filename))) {
        router.delete(route('backup.destroy', filename), {
            preserveScroll: true,
        });
    }
};

const formatFilename = (name) => {
    return name.replace('backup-', '').replace('.sql', '');
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.title" />

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 text-emerald-600 rounded-xl">
                        <Database :size="24" />
                    </div>
                    {{ translations.title }}
                </h1>
                <p class="text-slate-500 mt-2 font-medium">{{ translations.desc }}</p>
            </div>

            <button
                @click="runBackup"
                :disabled="isProcessing"
                class="flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all disabled:opacity-50 disabled:cursor-not-allowed"       
            >
                <Loader2 v-if="isProcessing" :size="18" class="animate-spin" />
                <Play v-else :size="18" />
                {{ isProcessing ? translations.starting_backup : translations.generate_button }}
            </button>
        </div>

        <!-- Info Card -->
        <div class="bg-blue-50 border border-blue-100 rounded-3xl p-6 mb-8 flex gap-4 items-start">
            <div class="p-2 bg-white rounded-xl text-blue-500 shadow-sm shrink-0">
                <Clock :size="20" />
            </div>
            <div>
                <h4 class="text-sm font-black text-blue-900 uppercase tracking-widest">{{ translations.queue_info_title }}</h4> 
                <p class="text-sm text-blue-700 mt-1 leading-relaxed font-medium">{{ translations.queue_info_desc }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <FileText :size="20" class="text-emerald-500" /> {{ translations.history_title }}
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/30">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.file_date_time }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.file_size }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.status }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">{{ translations.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="file in backups" :key="file.name" class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
                                        <FileText :size="20" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700">{{ formatFilename(file.name) }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium italic">{{ file.name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-sm font-bold text-slate-600">{{ file.size }}</span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100">
                                    <CheckCircle :size="12" /> {{ translations.ready }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex justify-end gap-2">
                                    <a :href="route('backup.download', file.name)" class="p-2 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-all" :title="translations.download">
                                        <Download :size="18" />
                                    </a>
                                    <button @click="deleteBackup(file.name)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" :title="translations.delete">
                                        <Trash2 :size="18" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="backups.length === 0">
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 bg-slate-50 rounded-full text-slate-200">
                                        <Database :size="48" />
                                    </div>
                                    <p class="text-slate-400 font-medium">{{ translations.no_backups }}</p>        
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-8 bg-slate-50/30 border-t border-slate-50">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">{{ translations.storage_location.replace(':path', '/storage/app/public/backups') }}</p>
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
