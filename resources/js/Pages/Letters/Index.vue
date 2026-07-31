<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import Modal from '@/Components/ui/Modal.vue';
import {
    Mail, Send, Inbox, Plus, Lock,
    Unlock, Eye, Calendar, User,
    Search, RotateCw, Loader2, Save, X,
    ChevronRight, Clock, ShieldCheck
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    letters: Object,
    members: Array,
    currentTab: String,
    myMemberId: Number,
    translations: Object,
    error: String,
});

defineOptions({ layout: Layout });

const showWriteModal = ref(false);
const selectedLetter = ref(null);

const writeForm = useForm({
    receiver_id: '',
    subject: '',
    content: '',
    unlock_type: 'immediate',
    unlock_at: '',
    unlock_value: '',
});

const switchTab = (tab) => {
    router.get(route('letters.index'), { tab }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const openLetter = (letter) => {
    if (isLocked(letter)) return;

    selectedLetter.value = letter;
    if (!letter.read_at && letter.receiver_id === props.myMemberId) {
        router.post(route('letters.read', letter.id), {}, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const isLocked = (letter) => {
    if (letter.unlock_type === 'immediate') return false;

    if (letter.unlock_type === 'date' && letter.unlock_at) {
        return new Date(letter.unlock_at) > new Date();
    }

    if (letter.unlock_type === 'age' && letter.unlock_value && letter.receiver_birthdate) {
        const birthDate = new Date(letter.receiver_birthdate);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age < letter.unlock_value;
    }

    // Legacy support
    if (!letter.unlock_type && letter.unlock_at) {
        return new Date(letter.unlock_at) > new Date();
    }

    return false;
};

const submitLetter = () => {
    writeForm.post(route('letters.store'), {
        onSuccess: () => {
            showWriteModal.value = false;
            writeForm.reset();
        },
    });
};

const getStatusColor = (letter) => {
    if (isLocked(letter)) return 'bg-amber-50 text-amber-600 border-amber-100';
    if (letter.read_at) return 'bg-slate-50 text-slate-400 border-slate-100';
    return 'bg-sky-50 text-sky-600 border-sky-100 shadow-sm';
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
                        <Mail :size="32" />
                    </div>
                    {{ translations.title }}
                </h1>
                <p class="text-slate-500 font-medium leading-relaxed">
                    {{ translations.desc }}
                </p>
            </div>

            <button @click="showWriteModal = true" class="flex items-center gap-2 px-6 py-4 bg-amber-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-amber-100 hover:bg-amber-600 transition-all active:scale-95 shrink-0">
                <Plus :size="16" /> {{ translations.write_button }}
            </button>
        </div>

        <div v-if="error" class="bg-rose-50 border-2 border-rose-100 p-8 rounded-[2rem] text-center mb-12">     
            <ShieldCheck :size="48" class="text-rose-400 mx-auto mb-4" />
            <h3 class="text-xl font-black text-rose-900 uppercase tracking-tight mb-2">{{ translations.heritage_required }}</h3>
            <p class="text-rose-600 font-medium">{{ error }}</p>
        </div>

        <template v-else>
            <!-- Tab Switcher -->
            <div class="flex justify-center mb-12">
                <div class="bg-white p-1.5 rounded-[1.5rem] border border-slate-100 shadow-sm flex items-center gap-1">
                    <button
                        @click="switchTab('inbox')"
                        class="flex items-center gap-2 px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                        :class="currentTab === 'inbox' ? 'bg-amber-500 text-white shadow-lg shadow-amber-100' : 'text-slate-400 hover:bg-slate-50 hover:text-amber-600'"
                    >
                        <Inbox :size="16" /> {{ translations.inbox }}
                    </button>
                    <button
                        @click="switchTab('sent')"
                        class="flex items-center gap-2 px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                        :class="currentTab === 'sent' ? 'bg-amber-500 text-white shadow-lg shadow-amber-100' : 'text-slate-400 hover:bg-slate-50 hover:text-amber-600'"
                    >
                        <Send :size="16" /> {{ translations.sent }}
                    </button>
                </div>
            </div>

            <!-- Letters List -->
            <div v-if="letters.data.length" class="space-y-4 animate-in fade-in slide-in-from-bottom-8 duration-700">
                <div v-for="letter in letters.data" :key="letter.id"
                    @click="openLetter(letter)"
                    class="group relative bg-white rounded-[2rem] border border-slate-100 p-6 flex items-center justify-between transition-all duration-300 hover:shadow-xl hover:shadow-amber-50 hover:border-amber-200 cursor-pointer overflow-hidden"
                    :class="{ 'opacity-75 grayscale-[0.5]': isLocked(letter) }"
                >
                    <!-- Background Decoration -->
                    <div v-if="!isLocked(letter) && !letter.read_at && currentTab === 'inbox'" class="absolute left-0 top-0 bottom-0 w-1.5 bg-amber-400"></div>

                    <div class="flex items-center gap-6 flex-1">
                        <!-- Avatar -->
                        <div class="w-16 h-16 rounded-2xl overflow-hidden bg-slate-50 border-2 border-white shadow-sm shrink-0">
                            <img v-if="currentTab === 'inbox' ? letter.sender_picture : letter.receiver_picture"
                                :src="currentTab === 'inbox' ? letter.sender_picture : letter.receiver_picture" 
                                class="w-full h-full object-cover">
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-300">  
                                <User :size="24" />
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-lg font-black text-slate-900 truncate uppercase tracking-tight">{{ letter.subject }}</h3>
                                <span v-if="isLocked(letter)" class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-lg text-[8px] font-black uppercase tracking-widest flex items-center gap-1">
                                    <Lock :size="10" /> {{ translations.time_capsule }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                <span class="flex items-center gap-1.5">
                                    <User :size="12" class="text-slate-300" />
                                    {{ currentTab === 'inbox' ? translations.from.replace(':name', letter.sender_name) : translations.to.replace(':name', letter.receiver_name) }}
                                </span>
                                <div class="w-1 h-1 bg-slate-200 rounded-full"></div>
                                <span class="flex items-center gap-1.5">
                                    <Calendar :size="12" class="text-slate-300" />
                                    {{ new Date(letter.created_at).toLocaleDateString() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 pr-4">
                        <div class="hidden sm:flex flex-col items-end">
                            <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest border" :class="getStatusColor(letter)">
                                {{ isLocked(letter) ? translations.locked : (letter.read_at ? translations.opened : translations.unread) }}      
                            </span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-amber-500 group-hover:text-white transition-all duration-300 shadow-sm">
                            <Lock v-if="isLocked(letter)" :size="18" />
                            <ChevronRight v-else :size="20" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-24 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">    
                <div class="w-24 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center text-slate-200 mx-auto mb-6 shadow-inner">
                    <Mail :size="48" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight">{{ translations.empty_archives }}</h3>
                <p class="text-slate-400 max-w-sm mx-auto font-medium">
                    {{ currentTab === 'inbox' ? translations.no_received : translations.no_sent }}
                </p>
                <button @click="showWriteModal = true" class="mt-8 text-amber-600 font-black uppercase tracking-widest text-xs hover:underline flex items-center gap-2 mx-auto">
                    <Plus :size="14" /> {{ translations.write_first }}
                </button>
            </div>

            <!-- Pagination -->
            <div v-if="letters.links && letters.links.length > 3" class="mt-12 flex justify-center">
                <nav class="flex gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
                    <template v-for="(link, index) in letters.links" :key="index">
                        <Link v-if="link.url" :href="link.url"
                            v-html="link.label"
                            class="px-4 h-10 flex items-center justify-center rounded-xl text-[10px] font-black uppercase transition-all"
                            :class="[ link.active ? 'bg-amber-500 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50' ]"
                        />
                    </template>
                </nav>
            </div>
        </template>

        <!-- Write Modal -->
        <Modal :show="showWriteModal" :title="translations.write_new" @close="showWriteModal = false">
            <form @submit.prevent="submitLetter" class="p-10 space-y-8">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.recipient }}</label>
                            <select v-model="writeForm.receiver_id" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-amber-500 outline-none transition-all">
                                <option value="">{{ translations.select_member }}</option>
                                <option v-for="m in members" :key="m.memberid" :value="m.memberid">{{ m.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.unlock_condition }}</label>
                            <select v-model="writeForm.unlock_type" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-amber-500 outline-none transition-all">
                                <option value="immediate">{{ translations.open_immediately }}</option>
                                <option value="date">{{ translations.unlock_date_type }}</option>
                                <option value="age">{{ translations.unlock_age_type }}</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="writeForm.unlock_type === 'date'" class="animate-in fade-in duration-300">       
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.unlock_date }}</label>
                        <input v-model="writeForm.unlock_at" type="date" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-amber-500 outline-none">
                        <p class="text-[9px] text-slate-400 mt-2 italic">{{ translations.date_note }}</p>
                    </div>

                    <div v-if="writeForm.unlock_type === 'age'" class="animate-in fade-in duration-300">        
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.unlock_age }}</label>
                        <input v-model="writeForm.unlock_value" type="number" min="1" max="150" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-amber-500 outline-none" placeholder="e.g. 18, 21, 50">
                        <p class="text-[9px] text-slate-400 mt-2 italic">{{ translations.age_note }}</p>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.subject }}</label>
                        <input v-model="writeForm.subject" type="text" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-amber-500 outline-none" :placeholder="translations.subject_placeholder">
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.content }}</label>
                        <textarea v-model="writeForm.content" rows="8" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-amber-500 outline-none" :placeholder="translations.content_placeholder"></textarea>
                    </div>
                </div>

                <div class="flex gap-4 pt-6 border-t border-slate-50">
                    <button type="button" @click="showWriteModal = false" class="flex-1 px-8 py-5 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 transition-all flex items-center justify-center gap-2">
                        <X :size="16" /> {{ translations.discard }}
                    </button>
                    <button type="submit" :disabled="writeForm.processing" class="flex-1 px-8 py-5 bg-amber-500 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-amber-100 hover:bg-amber-600 transition-all flex items-center justify-center gap-2">
                        <Loader2 v-if="writeForm.processing" :size="16" class="animate-spin" />
                        <Send v-else :size="16" /> {{ writeForm.processing ? translations.sending : translations.send_letter }}    
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Read Modal -->
        <Modal :show="!!selectedLetter" maxWidth="3xl" @close="selectedLetter = null">
            <div v-if="selectedLetter" class="p-12 relative">
                <!-- Watermark / Stamp -->
                <div class="absolute top-10 right-10 opacity-[0.03] rotate-12 pointer-events-none">
                    <Mail :size="200" stroke-width="1" />
                </div>

                <div class="relative z-10">
                    <div class="flex items-start justify-between mb-10 pb-10 border-b-2 border-dashed border-slate-100">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-white shadow-md"> 
                                <img :src="selectedLetter.sender_picture" class="w-full h-full object-cover">   
                            </div>
                            <div>
                                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ translations.from_desk }}</h3>
                                <p class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ selectedLetter.sender_name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ translations.archived_on }}</p>
                            <p class="text-sm font-bold text-slate-600">{{ new Date(selectedLetter.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' }) }}</p>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight mb-8 leading-tight">{{ selectedLetter.subject }}</h2>
                        <div class="prose prose-slate max-w-none">
                            <p class="text-slate-600 text-xl font-serif italic leading-relaxed whitespace-pre-line">
                                {{ selectedLetter.content }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-10 border-t-2 border-dashed border-slate-100 flex items-center justify-between text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">
                        <span>{{ translations.heritage_archives }}</span>
                        <div class="flex items-center gap-2">
                            <RotateCw :size="12" /> {{ translations.permanently_archived }}
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
.page-wrapper {
    margin: 0 auto;
}
</style>
