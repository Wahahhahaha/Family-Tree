<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Calendar, MapPin, ChevronLeft,
    Bell, Info, CheckCircle2, XCircle,
    HelpCircle, Users, Clock, Share2,
    User, Ghost
} from 'lucide-vue-next';

const props = defineProps({
    event: Object,
    myResponse: Object,
    translations: Object,
});

const form = useForm({
    status: props.myResponse?.status || '',
});

const submitResponse = (status) => {
    form.status = status;
    form.post(route('events.respond', props.event.id), {
        preserveScroll: true,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusColor = (status) => {
    return {
        'going': 'text-emerald-600 bg-emerald-50 border-emerald-100',
        'not_going': 'text-rose-600 bg-rose-50 border-rose-100',
        'maybe': 'text-amber-600 bg-amber-50 border-amber-100'
    }[status] || 'text-slate-600 bg-slate-50 border-slate-100';
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="`${event.title} - ${translations.title}`" />

        <div class="mb-8">
            <Link :href="route('events.index')" class="inline-flex items-center gap-2 text-slate-400 hover:text-indigo-600 font-bold text-sm transition-all group">
                <ChevronLeft :size="18" class="group-hover:-translate-x-1 transition-transform" />
                {{ translations.back_to_directory }}
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Event Header Card -->
                <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="p-10 md:p-14">
                        <div class="flex items-center gap-3 mb-8">
                            <span class="px-4 py-1.5 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-100 shadow-sm">
                                {{ translations.statuses[event.status] || event.status }}
                            </span>
                            <div class="w-1 h-1 bg-slate-200 rounded-full"></div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                <Users :size="12" /> {{ translations.family_gathering }}
                            </span>
                        </div>

                        <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-8 uppercase tracking-tight leading-tight">
                            {{ event.title }}
                        </h1>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10 pb-10 border-b border-slate-50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-slate-50 text-indigo-600 rounded-2xl shadow-sm"><Calendar :size="24" /></div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ translations.event_date_time.split(' & ')[0] }}</p>
                                    <p class="text-lg font-black text-slate-700">{{ formatDate(event.event_date) }}</p>
                                    <p class="text-sm font-bold text-slate-400 flex items-center gap-1.5 mt-1"> 
                                        <Clock :size="14" /> {{ translations.start_time }} {{ formatTime(event.event_date) }}       
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-slate-50 text-rose-500 rounded-2xl shadow-sm"><MapPin :size="24" /></div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ translations.venue }}</p>
                                    <p class="text-lg font-black text-slate-700">{{ event.location || translations.location_tba }}</p>
                                    <button v-if="event.location" class="text-xs font-black text-indigo-600 uppercase tracking-widest hover:underline mt-2">{{ translations.open_maps }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="prose prose-slate max-w-none">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                                {{ translations.event_description }}
                            </h4>
                            <p class="text-slate-600 text-lg leading-relaxed font-medium whitespace-pre-line">  
                                {{ event.description || translations.no_description }}   
                            </p>
                        </div>
                    </div>

                    <!-- Quick Actions Footer -->
                    <div class="px-10 py-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">{{ translations.family_bond_strong }}</span>
                        <button class="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-widest hover:underline">
                            <Share2 :size="14" /> {{ translations.share_event }}
                        </button>
                    </div>
                </div>

                <!-- Responses Section -->
                <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm p-10">
                    <div class="flex items-center justify-between mb-10">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">{{ translations.family_attendance }}</h3>
                            <p class="text-slate-400 text-sm font-medium mt-1">{{ translations.see_who_joining }}</p>
                        </div>
                        <div class="flex -space-x-3">
                            <div v-for="i in 3" :key="i" class="w-10 h-10 rounded-full border-4 border-white bg-slate-100 overflow-hidden shadow-sm">
                                <User :size="16" class="m-auto mt-2 text-slate-300" />
                            </div>
                            <div class="w-10 h-10 rounded-full border-4 border-white bg-indigo-600 flex items-center justify-center text-[10px] font-black text-white shadow-lg">
                                +{{ Math.max(0, event.responses.length - 3) }}
                            </div>
                        </div>
                    </div>

                    <div v-if="event.responses.length" class="space-y-4">
                        <div v-for="response in event.responses" :key="response.id"
                            class="flex items-center justify-between p-4 bg-slate-50/50 rounded-2xl border border-slate-50 hover:border-indigo-100 transition-all group">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-white border-2 border-white shadow-sm shrink-0 group-hover:scale-110 transition-transform">
                                    <img v-if="response.member.picture" :src="response.member.picture" class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                        <User :size="18" />
                                    </div>
                                </div>
                                <div>
                                    <p class="font-black text-slate-900 uppercase tracking-tight text-sm">{{ response.member.name }}</p>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">{{ translations.replied_on.replace(':date', new Date(response.updated_at).toLocaleDateString()) }}</p>
                                </div>
                            </div>
                            <span class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border shadow-sm" :class="getStatusColor(response.status)">
                                {{ translations.statuses[response.status] || response.status.replace('_', ' ') }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="py-12 text-center border-2 border-dashed border-slate-100 rounded-[2rem]">
                        <Ghost :size="32" class="text-slate-200 mx-auto mb-4" />
                        <p class="text-slate-400 font-medium italic">{{ translations.waiting_response }}</p>
                    </div>
                </div>
            </div>

            <!-- RSVP Sidebar (Only for Family Members) -->
            <div v-if="$page.props.auth.is_family_member" class="lg:col-span-1 space-y-6">
                <div class="bg-slate-900 rounded-[3rem] p-10 shadow-2xl shadow-indigo-200 sticky top-24 overflow-hidden">
                    <!-- Background Decoration -->
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/20 blur-3xl rounded-full"></div>
                    <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-rose-500/10 blur-3xl rounded-full"></div>

                    <div class="relative z-10 text-center">
                        <div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center text-white mx-auto mb-6 shadow-xl">
                            <Info :size="28" />
                        </div>
                        <h3 class="text-2xl font-black text-white uppercase tracking-tight mb-2">{{ translations.are_you_coming }}</h3>
                        <p class="text-slate-400 text-sm font-medium mb-10 leading-relaxed">{{ translations.rsvp_desc }}</p>

                        <div class="space-y-3">
                            <button
                                @click="submitResponse('going')"
                                :disabled="form.processing"
                                class="w-full py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] transition-all flex items-center justify-center gap-3"
                                :class="form.status === 'going' ? 'bg-emerald-500 text-white shadow-xl shadow-emerald-500/30' : 'bg-white/5 text-slate-300 hover:bg-white/10'"
                            >
                                <CheckCircle2 :size="18" /> {{ translations.yes_going }}
                            </button>
                            <button
                                @click="submitResponse('maybe')"
                                :disabled="form.processing"
                                class="w-full py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] transition-all flex items-center justify-center gap-3"
                                :class="form.status === 'maybe' ? 'bg-amber-500 text-white shadow-xl shadow-amber-500/30' : 'bg-white/5 text-slate-300 hover:bg-white/10'"
                            >
                                <HelpCircle :size="18" /> {{ translations.might_join }}
                            </button>
                            <button
                                @click="submitResponse('not_going')"
                                :disabled="form.processing"
                                class="w-full py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] transition-all flex items-center justify-center gap-3"
                                :class="form.status === 'not_going' ? 'bg-rose-500 text-white shadow-xl shadow-rose-500/30' : 'bg-white/5 text-slate-300 hover:bg-white/10'"
                            >
                                <XCircle :size="18" /> {{ translations.cant_make_it }}
                            </button>
                        </div>

                        <p v-if="myResponse" class="mt-8 text-[9px] font-black uppercase tracking-[0.2em] text-indigo-400 animate-pulse">
                            {{ translations.already_responded }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-wrapper {
    margin: 0 auto;
}
</style>
