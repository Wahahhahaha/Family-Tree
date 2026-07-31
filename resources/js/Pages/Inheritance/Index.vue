<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    Landmark, UserCheck, ShieldAlert, 
    Crown, History, Key, User, 
    ChevronRight, Save, RotateCw, Loader2,
    Lock, ArrowDownCircle, Info, ShieldCheck,
    AlertCircle, ChevronLeft
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    currentHeir: Object,
    members: Array,
    history: Object, // Changed from Array to Object to handle Laravel Paginator
    hasPinSet: Boolean,
    isSuperadmin: Boolean,
    translations: Object,
});

const form = useForm({
    heir_memberid: props.currentHeir?.memberid || '',
    pin: '',
});

const resetPinForm = useForm({});

const submitSuccession = () => {
    form.post(route('inheritance.set-heir'), {
        onSuccess: () => {
            form.pin = '';
        },
    });
};

const resetPin = () => {
    if (confirm(props.translations.reset_confirm)) {
        resetPinForm.post(route('inheritance.reset-pin'));
    }
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.title" />

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-black text-slate-900 flex items-center gap-3 mb-4">
                    <div class="p-2.5 bg-amber-100 text-amber-600 rounded-2xl">
                        <Landmark :size="32" />
                    </div>
                    {{ translations.title }}
                </h1>
                <p class="text-slate-500 font-medium leading-relaxed">
                    {{ translations.desc }}
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <button v-if="isSuperadmin && hasPinSet" @click="resetPin" :disabled="resetPinForm.processing" class="px-6 py-4 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 rounded-2xl flex items-center gap-3 shadow-sm transition-all font-bold text-sm disabled:opacity-50">
                    <Loader2 v-if="resetPinForm.processing" :size="16" class="animate-spin" />
                    <RotateCw v-else :size="16" /> {{ translations.reset_pin }}
                </button>
                <div class="px-6 py-4 bg-white border border-slate-100 rounded-2xl flex items-center gap-3 shadow-sm">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations.protocol_active }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Current Succession State -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Current Heir Card -->
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-amber-50/50 transition-all duration-500 overflow-hidden">
                    <div class="p-10 text-center">
                        <div class="mb-8 flex justify-center">
                            <div class="relative">
                                <div class="w-36 h-36 rounded-[2.5rem] overflow-hidden bg-slate-50 border-4 border-white shadow-2xl">
                                    <img v-if="currentHeir?.picture" :src="currentHeir.picture" class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-200">
                                        <User :size="56" />
                                    </div>
                                </div>
                                <div class="absolute -top-4 -right-4 w-14 h-14 bg-amber-500 text-white rounded-2xl flex items-center justify-center shadow-lg rotate-12 animate-in zoom-in-50 duration-500">
                                    <Crown :size="28" />
                                </div>
                            </div>
                        </div>

                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">{{ translations.current_successor }}</p>
                        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-6">
                            {{ currentHeir ? currentHeir.name : translations.succession_unset }}
                        </h2>

                        <div v-if="currentHeir" class="inline-flex items-center gap-2 px-5 py-2 bg-emerald-50 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-100 shadow-sm">
                            <ShieldCheck :size="14" /> {{ translations.authority_granted }}
                        </div>
                        <div v-else class="inline-flex items-center gap-2 px-5 py-2 bg-rose-50 text-rose-600 rounded-full text-[9px] font-black uppercase tracking-widest border border-rose-100 shadow-sm">
                            <AlertCircle :size="14" /> {{ translations.protocol_at_risk }}
                        </div>
                    </div>

                    <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-50 flex items-start gap-3">      
                        <span class="p-1.5 bg-white rounded-lg shadow-sm">
                            <Info :size="14" class="text-sky-500" />
                        </span>
                        <p class="text-[10px] font-bold text-slate-400 leading-relaxed uppercase tracking-wider">
                            {{ translations.info_desc }}
                        </p>
                    </div>
                </div>

                <!-- Security Protocol Notice -->
                <div class="p-8 bg-sky-50/50 rounded-[2rem] border-2 border-sky-100/50 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-[0.05] group-hover:scale-110 transition-transform duration-700 text-sky-600">
                        <Key :size="80" />
                    </div>
                    <h4 class="text-[10px] font-black text-sky-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <Lock :size="12" /> {{ translations.security_standards }}
                    </h4>
                    <p v-if="isSuperadmin" class="text-xs font-bold text-slate-500 leading-relaxed uppercase tracking-wider">
                        {{ translations.superadmin_override }}       
                    </p>
                    <p v-else class="text-xs font-bold text-slate-500 leading-relaxed uppercase tracking-wider">
                        {{ translations.security_desc }}
                    </p>
                </div>
            </div>

            <!-- Right: Succession Form & History -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Appointment Form -->
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-10 md:p-14 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-12 h-12 bg-sky-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-sky-100">
                            <Key :size="24" />
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">{{ translations.transfer_authority }}</h3>
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mt-1">{{ translations.appointment_protocol }}</p>
                        </div>
                    </div>

                    <form @submit.prevent="submitSuccession" class="space-y-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div class="space-y-4" :class="{ 'md:col-span-2': isSuperadmin }">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <User :size="12" /> {{ translations.target_member }}
                                </label>
                                <select v-model="form.heir_memberid" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-5 focus:ring-sky-500 focus:border-sky-200 outline-none transition-all appearance-none cursor-pointer">
                                    <option value="">{{ translations.select_successor }}</option>
                                    <option v-for="m in members" :key="m.memberid" :value="m.memberid">{{ m.name }}</option>
                                </select>
                                <div v-if="form.errors.heir_memberid" class="text-rose-500 text-[9px] font-black uppercase tracking-widest mt-2">{{ form.errors.heir_memberid }}</div>
                            </div>

                            <div v-if="!isSuperadmin" class="space-y-4">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <Lock :size="12" /> {{ translations.inheritance_pin }}
                                </label>
                                <div class="relative">
                                    <input v-model="form.pin" type="password" maxlength="4" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-5 focus:ring-sky-500 focus:border-sky-200 outline-none transition-all tracking-[0.8em]" placeholder="••••">
                                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300">      
                                        <ShieldAlert :size="20" />
                                    </div>
                                </div>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-2 italic">
                                    <span v-if="hasPinSet">{{ translations.verify_pin }}</span>
                                    <span v-else>{{ translations.create_pin }}</span>      
                                </p>
                                <div v-if="form.errors.pin" class="text-rose-500 text-[9px] font-black uppercase tracking-widest mt-2">{{ form.errors.pin }}</div>
                            </div>
                        </div>

                        <div class="pt-10 border-t border-slate-50 flex justify-end">
                            <button type="submit" :disabled="form.processing" class="px-12 py-5 bg-sky-600 text-white rounded-2xl font-black text-xs uppercase tracking-[0.3em] shadow-xl shadow-sky-100 hover:bg-sky-700 transition-all flex items-center gap-4 active:scale-95 disabled:opacity-50">
                                <Loader2 v-if="form.processing" :size="20" class="animate-spin" />
                                <Save v-else :size="20" /> {{ form.processing ? translations.authorizing : translations.confirm_succession }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Succession History -->
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-10">
                    <div class="flex items-center justify-between mb-10">
                        <div class="flex items-center gap-4">
                            <div class="p-2.5 bg-slate-50 text-slate-400 rounded-xl border border-slate-100">   
                                <History :size="22" />
                            </div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ translations.succession_logs }}</h3>
                        </div>
                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">{{ translations.heritage_archives }}</span>
                    </div>

                    <div v-if="history.data.length" class="space-y-4">
                        <div v-for="(entry, index) in history.data" :key="index" class="flex items-center justify-between p-6 bg-slate-50/30 rounded-[1.5rem] border border-slate-100 hover:border-sky-200 hover:bg-white transition-all group">
                            <div class="flex items-center gap-6">
                                <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-amber-500 shadow-sm group-hover:scale-110 group-hover:bg-amber-50 transition-all">     
                                    <Crown :size="22" />
                                </div>
                                <div>
                                    <p class="font-black text-slate-900 uppercase tracking-tight text-base">{{ entry.leader_name }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ translations.elevated_on.replace(':date', new Date(entry.changed_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' })) }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="px-4 py-1.5 bg-white border border-slate-100 rounded-full text-[9px] font-black uppercase tracking-widest text-slate-400 shadow-sm">
                                    {{ entry.source }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-16 text-center border-4 border-dashed border-slate-50 rounded-[2.5rem]">
                        <RotateCw :size="32" class="text-slate-100 mx-auto mb-4" />
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">{{ translations.history_empty }}</p>
                    </div>
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

select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1.25rem center;
    background-size: 1.5em;
}
</style>
