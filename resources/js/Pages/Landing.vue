<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import Footer from '@/Components/ui/Footer.vue';
import LanguageToggle from '@/Components/LanguageToggle.vue';

defineProps({
    settings: Object,
    translations: Object,
});

const { systemname } = usePage().props;

defineOptions({ layout: null });
</script>

<template>
    <div class="min-h-screen bg-white flex flex-col">
        <Head :title="settings?.family_name || translations.welcome" />

        <!-- Floating Language Toggle -->
        <div class="fixed top-6 right-6 z-50">
            <LanguageToggle />
        </div>

        <div class="flex-1">
            <!-- Hero Section -->
            <header class="relative h-[90vh] flex items-center justify-center overflow-hidden">
                <div class="absolute inset-0 z-0">
                    <img v-if="settings?.head_of_family_photo" 
                        :src="settings.head_of_family_photo"
                        class="w-full h-full object-cover opacity-20 scale-105 blur-sm"
                        alt="Background">
                    <div class="absolute inset-0 bg-gradient-to-b from-sky-50/50 via-white to-white"></div>     
                </div>

                <div class="container mx-auto px-6 relative z-10 text-center">
                    <div class="mb-8 flex justify-center">
                        <div class="w-32 h-32 rounded-3xl overflow-hidden shadow-2xl border-4 border-white">    
                            <img v-if="settings?.head_of_family_photo" :src="settings.head_of_family_photo" class="w-full h-full object-cover">
                            <div v-else class="w-full h-full bg-sky-100 flex items-center justify-center text-sky-500 text-4xl font-black">
                                {{ settings?.family_name?.charAt(0) || 'F' }}
                            </div>
                        </div>
                    </div>

                    <h1 class="text-5xl md:text-7xl font-black text-slate-900 mb-6 tracking-tight">
                        {{ settings?.family_name || translations.hero_title }}
                    </h1>

                    <p class="text-xl text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed font-medium">      
                        {{ settings?.description || translations.hero_description }}
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link :href="route('login')" class="px-10 py-4 bg-sky-600 text-white rounded-2xl font-bold text-lg shadow-xl shadow-sky-200 hover:bg-sky-700 hover:-translate-y-1 transition-all">
                            {{ translations.explore_button }}
                        </Link>
                    </div>
                </div>
            </header>

            <!-- Message from Head Section -->
            <section class="py-24 bg-slate-50">
                <div class="container mx-auto px-6">
                    <div class="max-w-4xl mx-auto bg-white p-12 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 text-sky-100 opacity-50">
                            <svg width="120" height="120" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14.017 21L14.017 18C14.017 16.8954 14.9124 16 16.017 16H19.017C19.5693 16 20.017 15.5523 20.017 15V9C20.017 8.44772 19.5693 8 19.017 8H15.017C14.4647 8 14.017 8.44772 14.017 9V11.5C14.017 12.0523 13.5693 12.5 13.017 12.5H11.017C10.4647 12.5 10.017 12.0523 10.017 11.5V9C10.017 7.34315 11.3601 6 13.017 6H19.017C20.6739 6 22.017 7.34315 22.017 9V15C22.017 16.6569 20.6739 18 19.017 18H16.017L16.017 21H14.017ZM2.01697 21L2.01697 18C2.01697 16.8954 2.9124 16 4.01697 16H7.01697C7.56925 16 8.01697 15.5523 8.01697 15V9C8.01697 8.44772 7.56925 8 7.01697 8H3.01697C2.46468 8 2.01697 8.44772 2.01697 9V11.5C2.01697 12.0523 1.56925 12.5 1.01697 12.5H-0.983032C-1.53532 12.5 -1.98303 12.0523 -1.98303 11.5V9C-1.98303 7.34315 -0.639886 6 1.01697 6H7.01697C8.67382 6 10.017 7.34315 10.017 9V15C10.017 16.6569 8.67382 18 7.01697 18H4.01697L4.01697 21H2.01697Z" />  
                            </svg>
                        </div>

                        <div class="relative z-10">
                            <h2 class="text-sm font-black text-sky-600 uppercase tracking-widest mb-6">{{ translations.word_from_elders }}</h2>
                            <blockquote class="text-2xl md:text-3xl font-bold text-slate-800 italic mb-10 leading-snug">
                                "{{ settings?.head_of_family_message || translations.elders_message }}"
                            </blockquote>

                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-100">
                                    <img v-if="settings?.head_of_family_photo" :src="settings.head_of_family_photo" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900">{{ settings?.head_of_family_name || translations.head_of_family }}</h4>
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ translations.patriarch_matriarch }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contributors Section (formerly footer) -->
            <section class="py-16 bg-white border-t border-slate-50">
                <div class="container mx-auto px-6">
                    <div class="flex flex-wrap justify-center gap-12 text-center">
                        <div v-if="settings?.created_by_name" class="flex flex-col items-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">{{ translations.created_by }}</span>
                            <div class="flex items-center gap-3 bg-slate-50 pl-2 pr-4 py-2 rounded-full border border-slate-100">
                                <img v-if="settings.created_by_photo" :src="settings.created_by_photo" class="w-8 h-8 rounded-full object-cover">
                                <span class="text-sm font-bold text-slate-700">{{ settings.created_by_name }}</span>
                            </div>
                        </div>

                        <div v-if="settings?.designed_by_name" class="flex flex-col items-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">{{ translations.designed_by }}</span>
                            <div class="flex items-center gap-3 bg-slate-50 pl-2 pr-4 py-2 rounded-full border border-slate-100">
                                <img v-if="settings.designed_by_photo" :src="settings.designed_by_photo" class="w-8 h-8 rounded-full object-cover">
                                <span class="text-sm font-bold text-slate-700">{{ settings.designed_by_name }}</span>
                            </div>
                        </div>

                        <div v-if="settings?.approved_by_name" class="flex flex-col items-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">{{ translations.approved_by }}</span>
                            <div class="flex items-center gap-3 bg-slate-50 pl-2 pr-4 py-2 rounded-full border border-slate-100">
                                <img v-if="settings.approved_by_photo" :src="settings.approved_by_photo" class="w-8 h-8 rounded-full object-cover">
                                <span class="text-sm font-bold text-slate-700">{{ settings.approved_by_name }}</span>
                            </div>
                        </div>

                        <div v-if="settings?.acknowledged_by_name" class="flex flex-col items-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">{{ translations.acknowledged_by }}</span>
                            <div class="flex items-center gap-3 bg-slate-50 pl-2 pr-4 py-2 rounded-full border border-slate-100">
                                <img v-if="settings.acknowledged_by_photo" :src="settings.acknowledged_by_photo" class="w-8 h-8 rounded-full object-cover">
                                <span class="text-sm font-bold text-slate-700">{{ settings.acknowledged_by_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Global Full-Width Footer -->
        <Footer :systemname="systemname" />
    </div>
</template>
