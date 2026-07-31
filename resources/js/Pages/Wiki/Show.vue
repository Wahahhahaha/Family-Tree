<script setup>
import { Head, Link } from '@inertiajs/vue3';
import {
    ShieldCheck, MapPin, User,
    Briefcase, Calendar, Heart,
    ChevronLeft, Mail, Phone, Home
} from 'lucide-vue-next';

defineProps({
    member: Object,
    translations: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="`${member.name} - ${translations.title}`" />

        <div class="mb-8">
            <Link :href="route('wiki.index')" class="inline-flex items-center gap-2 text-slate-400 hover:text-indigo-600 font-bold text-sm transition-all group">
                <ChevronLeft :size="18" class="group-hover:-translate-x-1 transition-transform" />
                {{ translations.back_to_wiki }}
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Column -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-[3rem] border border-slate-100 shadow-xl shadow-indigo-100/30 overflow-hidden sticky top-24">
                    <div class="aspect-square relative overflow-hidden bg-slate-100 border-b-4 border-indigo-50">
                        <img v-if="member.picture" :src="member.picture" class="w-full h-full object-cover">    
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-300">      
                            <User :size="80" />
                        </div>
                    </div>
                    <div class="p-8 text-center">
                        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-2">{{ member.name }}</h2>
                        <div class="flex items-center justify-center gap-2 mb-6">
                            <span class="px-3 py-1 bg-sky-50 text-sky-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-sky-100">
                                {{ translations[member.life_status] || member.life_status }}
                            </span>
                            <span v-if="member.gender" class="px-3 py-1 bg-slate-50 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-widest border border-slate-100">
                                {{ translations[member.gender] || member.gender }}
                            </span>
                        </div>

                        <div class="space-y-4 text-left border-t border-slate-50 pt-6">
                            <div v-if="member.email" class="flex items-center gap-3 text-sm text-slate-600 font-medium">
                                <div class="p-2 bg-blue-50 text-blue-500 rounded-xl"><Mail :size="16" /></div>  
                                <span class="truncate">{{ member.email }}</span>
                            </div>
                            <div v-if="member.phonenumber" class="flex items-center gap-3 text-sm text-slate-600 font-medium">
                                <div class="p-2 bg-indigo-50 text-indigo-500 rounded-xl"><Phone :size="16" /></div>
                                <span>{{ member.phonenumber }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Biography / Info Card -->
                <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm p-10">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8 flex items-center gap-2">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                        {{ translations.member_biography }}
                    </h3>

                    <div class="prose prose-slate max-w-none">
                        <p class="text-slate-600 leading-relaxed text-lg font-medium">
                            {{ translations.bio_summary
                                .replace(':name', member.name)
                                .replace(':place', member.birthplace || translations.unknown_location)
                                .replace(':date', member.birthdate ? member.birthdate.split(/[T ]/)[0] : translations.unknown_date) }}
                            <template v-if="member.job">
                                {{ translations.bio_job
                                    .replace(':gender', member.gender === 'male' ? translations.male : translations.female)
                                    .replace(':job', member.job) }}
                            </template>
                        </p>
                        <div class="bg-slate-50 rounded-3xl p-8 mt-8 border border-slate-100 italic text-slate-500 font-medium">
                            {{ translations.family_quote }}
                        </div>
                    </div>
                </div>

                <!-- Life Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="p-2.5 bg-rose-100 text-rose-600 rounded-2xl"><MapPin :size="20" /></div>
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">{{ translations.birthplace }}</h4>
                        </div>
                        <p class="text-slate-600 font-bold">{{ member.birthplace || translations.not_recorded }}</p>       
                    </div>

                    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="p-2.5 bg-sky-100 text-sky-600 rounded-2xl"><Home :size="20" /></div>    
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">{{ translations.residence }}</h4>
                        </div>
                        <p class="text-slate-600 font-bold">{{ member.address || translations.not_recorded }}</p>
                    </div>

                    <div v-if="member.life_status === 'deceased'" class="bg-slate-900 rounded-[2.5rem] border border-slate-800 shadow-sm p-8 col-span-full">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="p-2.5 bg-slate-800 text-slate-400 rounded-2xl"><Calendar :size="20" /></div>
                            <h4 class="text-sm font-black text-white uppercase tracking-widest">{{ translations.memorial_info }}</h4>
                        </div>
                        <p class="text-slate-400 font-medium leading-relaxed">
                            {{ translations.resting_in_peace.replace(':date', member.deaddate ? member.deaddate.split(/[T ]/)[0] : translations.unknown_date) }}
                            <span v-if="member.grave_location_url" class="block mt-2 text-indigo-400 font-bold">
                                {{ translations.grave_location.replace(':url', member.grave_location_url) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-wrapper {
    max-width: 1440px;
    margin: 0 auto;
}
</style>
