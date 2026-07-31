<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import { 
    Calendar as CalendarIcon, MapPin, ChevronRight, 
    Bell, Sparkles, Plus, Search, Wind, Ghost,
    Clock, Info, Save, RotateCw, Loader2
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps({
    events: Object,
    translations: Object,
});

const showProposeModal = ref(false);

const form = useForm({
    title: '',
    event_date: '',
    location: '',
    description: '',
});

const submitProposal = () => {
    form.post(route('events.store'), {
        onSuccess: () => {
            showProposeModal.value = false;
            form.reset();
        },
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.title" />

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-black text-slate-900 flex items-center gap-3 mb-4">
                    <div class="p-2.5 bg-indigo-100 text-indigo-600 rounded-2xl">
                        <CalendarIcon :size="32" />
                    </div>
                    {{ translations.title }}
                </h1>
                <p class="text-slate-500 font-medium leading-relaxed">
                    {{ translations.desc }}
                </p>
            </div>

            <button
                v-if="$page.props.auth.is_family_member"
                @click="showProposeModal = true"
                class="flex items-center gap-2 px-6 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95 shrink-0"
            >
                <Plus :size="16" /> {{ translations.propose_new }}
            </button>
        </div>

        <!-- Proposal Modal -->
        <Modal :show="showProposeModal" :title="translations.propose_title" @close="showProposeModal = false">
            <form @submit.prevent="submitProposal" class="p-10 space-y-8">
                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.event_title }}</label>
                        <input v-model="form.title" type="text" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-indigo-500 outline-none transition-all" :placeholder="translations.placeholder_title">
                        <div v-if="form.errors.title" class="text-rose-500 text-[10px] mt-1.5 font-bold uppercase tracking-wider">{{ form.errors.title }}</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.event_date_time }}</label>
                            <input v-model="form.event_date" type="datetime-local" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-indigo-500 outline-none">
                            <div v-if="form.errors.event_date" class="text-rose-500 text-[10px] mt-1.5 font-bold uppercase tracking-wider">{{ form.errors.event_date }}</div>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.venue_location }}</label>
                            <input v-model="form.location" type="text" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-indigo-500 outline-none" :placeholder="translations.placeholder_venue">
                            <div v-if="form.errors.location" class="text-rose-500 text-[10px] mt-1.5 font-bold uppercase tracking-wider">{{ form.errors.location }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.description }}</label>
                        <textarea v-model="form.description" rows="4" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-indigo-500 outline-none" :placeholder="translations.placeholder_desc"></textarea>
                        <div v-if="form.errors.description" class="text-rose-500 text-[10px] mt-1.5 font-bold uppercase tracking-wider">{{ form.errors.description }}</div>
                    </div>
                </div>

                <div class="flex gap-4 pt-6 border-t border-slate-50">
                    <button type="button" @click="showProposeModal = false" class="flex-1 px-8 py-5 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 transition-all flex items-center justify-center gap-2">
                        <RotateCw :size="16" /> {{ translations.discard }}
                    </button>
                    <button type="submit" :disabled="form.processing" class="flex-1 px-8 py-5 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center justify-center gap-2">
                        <Loader2 v-if="form.processing" :size="16" class="animate-spin" />
                        <Save v-else :size="16" /> {{ form.processing ? translations.submitting : translations.submit_proposal }}  
                    </button>
                </div>
            </form>
        </Modal>

        <div v-if="events.data.length" class="grid grid-cols-1 gap-6 animate-in fade-in slide-in-from-bottom-8 duration-700">
            <Link v-for="event in events.data" :key="event.id" :href="route('events.show', event.id)"
                class="group block bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-indigo-100 hover:border-indigo-100 transition-all duration-500 overflow-hidden">
                <div class="p-3 flex flex-col lg:flex-row lg:items-stretch">
                    <!-- Date Badge -->
                    <div class="lg:w-56 bg-slate-50 rounded-[2rem] p-8 flex flex-col items-center justify-center text-center group-hover:bg-indigo-600 transition-all duration-500">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 group-hover:text-indigo-200 transition-colors">{{ formatDate(event.event_date).split(' ')[1] }}</span>
                        <span class="text-5xl font-black text-slate-900 group-hover:text-white transition-colors">{{ formatDate(event.event_date).split(' ')[0] }}</span>
                        <span class="text-sm font-bold text-slate-500 mt-2 group-hover:text-indigo-100 transition-colors">{{ formatDate(event.event_date).split(' ')[2] }}</span>
                    </div>

                    <!-- Content -->
                    <div class="flex-grow p-10 flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="px-4 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-100 shadow-sm">
                                {{ translations.statuses[event.status] || event.status }}
                            </span>
                            <div class="w-1 h-1 bg-slate-200 rounded-full"></div>
                            <span class="flex items-center gap-1.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <Sparkles :size="12" class="text-amber-400" /> {{ translations.upcoming_event }}
                            </span>
                        </div>

                        <h2 class="text-3xl font-black text-slate-900 group-hover:text-indigo-600 transition-colors mb-6 leading-tight uppercase tracking-tight">{{ event.title }}</h2>

                        <div class="flex flex-wrap items-center gap-8">
                            <div class="flex items-center gap-3 text-sm font-bold text-slate-500">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-colors">
                                    <Bell :size="18" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-300">{{ translations.start_time }}</span>
                                    <span>{{ formatTime(event.event_date) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 text-sm font-bold text-slate-500">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-colors">
                                    <MapPin :size="18" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-300">{{ translations.venue }}</span>
                                    <span>{{ event.location || translations.location_tba }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Arrow Action -->
                    <div class="hidden lg:flex items-center px-12 border-l border-slate-50 group-hover:border-indigo-50 transition-colors">
                        <div class="w-14 h-14 rounded-[1.5rem] bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-indigo-600 group-hover:text-white group-hover:rotate-[-45deg] transition-all duration-500 shadow-sm group-hover:shadow-xl group-hover:shadow-indigo-200">
                            <ChevronRight :size="28" />
                        </div>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Empty State -->
        <div v-else class="py-24 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">        
            <div class="w-24 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center text-slate-200 mx-auto mb-6 shadow-inner">
                <CalendarIcon :size="48" />
            </div>
            <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight">{{ translations.quiet_horizon }}</h3>     
            <p class="text-slate-400 max-w-sm mx-auto font-medium">{{ translations.no_upcoming }}</p>
            <button @click="showProposeModal = true" class="mt-8 text-indigo-600 font-black uppercase tracking-widest text-xs hover:underline flex items-center gap-2 mx-auto">
                <Plus :size="14" /> {{ translations.start_tradition }}
            </button>
        </div>

        <!-- Pagination -->
        <div v-if="events.links && events.links.length > 3" class="mt-16 flex justify-center">
            <nav class="flex gap-3 bg-white p-2 rounded-[1.5rem] border border-slate-100 shadow-sm">
                <template v-for="(link, index) in events.links" :key="index">
                    <Link v-if="link.url" :href="link.url"
                        v-html="link.label"
                        class="px-5 h-12 flex items-center justify-center rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                        :class="[
                            link.active ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-400 hover:bg-slate-50 hover:text-indigo-600'
                        ]"
                    />
                    <span v-else
                        v-html="link.label"
                        class="px-5 h-12 flex items-center justify-center rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-200 cursor-not-allowed"
                    />
                </template>
            </nav>
        </div>
    </div>
</template>

<style scoped>
.page-wrapper {
    margin: 0 auto;
}
</style>
