<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import {
    ShieldCheck, UserCheck, Search, Info,
    FileText, CheckCircle2, XCircle,
    Clock, AlertCircle, Eye, User,
    MoreVertical, Save, RotateCw, Loader2,
    ArrowRight, MessageSquare, Download,
    Trash2, Upload
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    validations: Array,
    stats: Object,
    targetMember: Object,
    requestDeletion: Boolean,
    isAdmin: Boolean,
    translations: Object,
});

const selectedRequest = ref(null);
const showActionModal = ref(false);
const showRequestModal = ref(props.requestDeletion);
const filterStatus = ref('all');

const form = useForm({
    status: '',
    admin_notes: '',
});

const deleteRequestForm = useForm({
    reason: '',
    document: null,
});

const submitDeleteRequest = () => {
    deleteRequestForm.post(route('family-members.request-deletion', props.targetMember.memberid), {
        onSuccess: () => {
            showRequestModal.value = false;
            router.visit(route('validation.index'));
        },
    });
};

const filteredValidations = computed(() => {
    if (filterStatus.value === 'all') return props.validations;
    return props.validations.filter(v => v.status === filterStatus.value);
});

const openAction = (request, status) => {
    selectedRequest.value = request;
    form.status = status;
    form.admin_notes = '';
    showActionModal.value = true;
};

const submitAction = () => {
    form.post(route('validation.update-status', selectedRequest.value.id), {
        onSuccess: () => {
            showActionModal.value = false;
            selectedRequest.value = null;
        },
    });
};

const getStatusStyles = (status) => {
    return {
        'pending': 'bg-amber-50 text-amber-600 border-amber-100',
        'approved': 'bg-emerald-50 text-emerald-600 border-emerald-100',
        'rejected': 'bg-rose-50 text-rose-600 border-rose-100'
    }[status];
};

const getActionLabel = (type) => {
    return props.translations.action_types[type] || type;
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="isAdmin ? translations.title_admin : translations.title_user" />

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-black text-slate-900 flex items-center gap-3 mb-4">
                    <div class="p-2.5 bg-sky-100 text-sky-600 rounded-2xl">
                        <ShieldCheck :size="32" />
                    </div>
                    {{ isAdmin ? translations.title_admin : translations.title_user }}
                </h1>
                <p class="text-slate-500 font-medium leading-relaxed">
                    {{ isAdmin ? translations.desc_admin : translations.desc_user }}
                </p>
            </div>

            <div class="flex items-center gap-2 bg-white p-1.5 rounded-2xl border border-slate-100 shadow-sm">  
                <button v-for="s in ['all', 'pending', 'approved', 'rejected']" :key="s"
                    @click="filterStatus = s"
                    class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                    :class="filterStatus === s ? 'bg-sky-600 text-white shadow-lg shadow-sky-100' : 'text-slate-400 hover:bg-slate-50'"
                >
                    {{ translations[s] }}
                </button>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div v-for="(val, key) in stats" :key="key" class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm group hover:shadow-xl transition-all duration-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-2xl" :class="getStatusStyles(key)">
                        <component :is="key === 'pending' ? Clock : (key === 'approved' ? CheckCircle2 : XCircle)" :size="24" />
                    </div>
                    <span class="text-4xl font-black text-slate-900 group-hover:scale-110 transition-transform">{{ val }}</span>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations.stats_title.replace(':status', translations[key]) }}</p>
            </div>
        </div>

        <!-- Validations List -->
        <div v-if="filteredValidations.length" class="space-y-6 animate-in fade-in slide-in-from-bottom-8 duration-700">
            <div v-for="req in filteredValidations" :key="req.id"
                class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:border-sky-100 transition-all duration-500 overflow-hidden"
            >
                <div class="p-8 md:p-10 flex flex-col lg:flex-row lg:items-start gap-10">
                    <!-- Requester & Target -->
                    <div class="flex items-center gap-6 lg:w-1/3 shrink-0">
                        <div class="flex -space-x-4">
                            <div class="w-16 h-16 rounded-2xl border-4 border-white bg-slate-50 overflow-hidden shadow-md">
                                <img v-if="req.requester_picture" :src="req.requester_picture" class="w-full h-full object-cover">
                                <User v-else class="m-auto mt-4 text-slate-300" :size="24" />
                            </div>
                            <div class="w-16 h-16 rounded-2xl border-4 border-white bg-slate-100 overflow-hidden shadow-md flex items-center justify-center">
                                <ArrowRight :size="16" class="text-slate-400" />
                            </div>
                            <div class="w-16 h-16 rounded-2xl border-4 border-white bg-slate-50 overflow-hidden shadow-md">
                                <img v-if="req.target_picture" :src="req.target_picture" class="w-full h-full object-cover">
                                <User v-else class="m-auto mt-4 text-slate-300" :size="24" />
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ translations.request_by }}</p>
                            <p class="font-black text-slate-900 text-lg leading-tight">{{ req.requester_name }}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">{{ translations.for_target.replace(':name', req.target_name || translations.system) }}</p>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="flex-1 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border shadow-sm" :class="getStatusStyles(req.status)">
                                {{ translations[req.status] }}
                            </span>
                            <div class="w-1 h-1 bg-slate-200 rounded-full"></div>
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                                <AlertCircle :size="12" class="text-rose-500" /> {{ getActionLabel(req.action_type) }}
                            </span>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 relative">
                            <MessageSquare :size="16" class="absolute top-4 right-4 text-slate-200" />
                            <p class="text-sm font-medium text-slate-600 italic leading-relaxed">
                                "{{ req.reason || translations.no_reason }}"
                            </p>
                        </div>

                        <div v-if="req.document_path" class="flex items-center gap-3">
                            <a :href="req.document_path" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-widest border border-emerald-100 hover:bg-emerald-600 hover:text-white transition-all">
                                <FileText :size="14" /> {{ translations.view_documents }}
                            </a>
                        </div>
                    </div>

                    <!-- Actions (Only for Pending) -->
                    <div v-if="req.status === 'pending' && isAdmin" class="flex flex-row lg:flex-col gap-3 shrink-0">
                        <button @click="openAction(req, 'approved')" class="flex-1 lg:flex-none px-6 py-4 bg-emerald-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-emerald-100 hover:bg-emerald-600 transition-all flex items-center justify-center gap-2">
                            <CheckCircle2 :size="16" /> {{ translations.approve }}
                        </button>
                        <button @click="openAction(req, 'rejected')" class="flex-1 lg:flex-none px-6 py-4 bg-white text-rose-500 border border-rose-100 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-rose-50 transition-all flex items-center justify-center gap-2">
                            <XCircle :size="16" /> {{ translations.reject }}
                        </button>
                    </div>

                    <!-- Processed Date -->
                    <div v-else-if="req.status !== 'pending'" class="text-right shrink-0">
                        <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mb-2">{{ translations.verified_on }}</p>
                        <p class="text-xs font-bold text-slate-400 uppercase">{{ new Date(req.verified_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}</p>
                        <div v-if="req.admin_notes" class="mt-4 text-[10px] text-slate-400 italic max-w-[200px] text-right">
                            {{ translations.admin_note.replace(':note', req.admin_notes) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="py-24 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">        
            <div class="w-24 h-24 bg-sky-50 rounded-[2.5rem] flex items-center justify-center text-sky-200 mx-auto mb-6 shadow-inner">
                <UserCheck :size="48" />
            </div>
            <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight">{{ translations.empty_title }}</h3>
            <p class="text-slate-400 max-w-sm mx-auto font-medium">{{ translations.empty_desc.replace(':status', filterStatus !== 'all' ? translations[filterStatus] : '') }}</p>
        </div>

        <!-- Action Modal -->
        <Modal :show="showActionModal" :title="translations.confirm_title" @close="showActionModal = false">
            <div v-if="selectedRequest" class="p-10 space-y-8">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 mx-auto rounded-[1.5rem] flex items-center justify-center mb-6" :class="getStatusStyles(form.status)">
                        <component :is="form.status === 'approved' ? CheckCircle2 : XCircle" :size="40" />      
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-2">
                        {{ translations.confirm_action.replace(':action', translations[form.status]) }}
                    </h3>
                    <p class="text-slate-400 font-medium">{{ translations.confirm_desc.replace(':type', getActionLabel(selectedRequest.action_type)).replace(':name', selectedRequest.requester_name) }}</p>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">{{ translations.admin_notes_label }}</label>
                    <textarea v-model="form.admin_notes" rows="4" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-5 focus:ring-sky-500 outline-none transition-all" :placeholder="translations.admin_notes_placeholder"></textarea>
                </div>

                <div class="flex gap-4 pt-6 border-t border-slate-50">
                    <button @click="showActionModal = false" class="flex-1 px-8 py-5 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 transition-all flex items-center justify-center gap-2">
                        {{ translations.cancel }}
                    </button>
                    <button @click="submitAction" :disabled="form.processing" class="flex-1 px-8 py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl transition-all flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50 text-white"
                        :class="form.status === 'approved' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-100' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-100'"
                    >
                        <Loader2 v-if="form.processing" :size="18" class="animate-spin" />
                        <component v-else :is="form.status === 'approved' ? Save : AlertCircle" :size="18" />   
                        {{ form.processing ? translations.processing : translations.confirm_status.replace(':status', translations[form.status]) }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Request Deletion Modal -->
        <Modal :show="showRequestModal" :title="translations.request_deletion_title" @close="showRequestModal = false">      
            <form @submit.prevent="submitDeleteRequest" class="p-10 space-y-8">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-rose-50 text-rose-600 rounded-[1.5rem] flex items-center justify-center mx-auto mb-6 shadow-sm border border-rose-100">
                        <Trash2 :size="40" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-2">{{ translations.request_removal }}</h3>
                    <p class="text-slate-400 font-medium">{{ translations.removal_desc.replace(':name', targetMember?.name) }}</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.reason_label }}</label>
                        <textarea v-model="deleteRequestForm.reason" rows="4" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-rose-500 outline-none transition-all" :placeholder="translations.reason_placeholder"></textarea>
                        <div v-if="deleteRequestForm.errors.reason" class="text-rose-500 text-[10px] mt-1.5 font-bold uppercase tracking-wider">{{ deleteRequestForm.errors.reason }}</div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.document_label }}</label>
                        <div class="relative group">
                            <input type="file" @input="deleteRequestForm.document = $event.target.files[0]" class="absolute inset-0 opacity-0 cursor-pointer z-10" accept=".pdf,image/*">
                            <div class="py-10 border-2 border-dashed border-slate-200 rounded-[2rem] flex flex-col items-center justify-center bg-slate-50 group-hover:bg-white group-hover:border-rose-300 transition-all">    
                                <div class="p-4 bg-white text-rose-500 rounded-2xl shadow-sm mb-4">
                                    <Upload v-if="!deleteRequestForm.document" :size="24" />
                                    <FileText v-else :size="24" class="text-emerald-500" />
                                </div>
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">     
                                    {{ deleteRequestForm.document ? deleteRequestForm.document.name : translations.document_placeholder }}
                                </p>
                            </div>
                        </div>
                        <div v-if="deleteRequestForm.errors.document" class="text-rose-500 text-[10px] mt-1.5 font-bold uppercase tracking-wider">{{ deleteRequestForm.errors.document }}</div>
                    </div>
                </div>

                <div class="flex gap-4 pt-6 border-t border-slate-50">
                    <button type="button" @click="showRequestModal = false" class="flex-1 px-8 py-5 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 transition-all">   
                        {{ translations.cancel }}
                    </button>
                    <button type="submit" :disabled="deleteRequestForm.processing" class="flex-1 px-8 py-5 bg-rose-600 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-rose-100 hover:bg-rose-700 transition-all flex items-center justify-center gap-2">
                        <Loader2 v-if="deleteRequestForm.processing" :size="16" class="animate-spin" />
                        <Save v-else :size="16" /> {{ deleteRequestForm.processing ? translations.submitting : translations.submit_request }}
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>

<style scoped>
.page-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}
</style>
