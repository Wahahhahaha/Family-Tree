<script setup>
import { ref, watch, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Search, ShieldCheck, MapPin, 
    User, Briefcase, ChevronRight, 
    Wind, Ghost, Users, Plus, Loader2
} from 'lucide-vue-next';

const props = defineProps({
    initialMembers: Object, // Changed from members to initialMembers (paginated object)
    filters: Object,
    translations: Object,
});

const membersList = ref([...props.initialMembers.data]);
const nextUrl = ref(props.initialMembers.next_page_url);
const isLoading = ref(false);
const search = ref(props.filters.search || '');

// Throttle/Debounce search
let timeout = null;
watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('wiki.index'), { search: value }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onSuccess: (page) => {
                membersList.value = [...page.props.initialMembers.data];
                nextUrl.value = page.props.initialMembers.next_page_url;
            }
        });
    }, 300);
});

const loadMore = async () => {
    if (!nextUrl.value || isLoading.value) return;

    isLoading.value = true;
    try {
        const response = await fetch(nextUrl.value, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await response.json();

        membersList.value = [...membersList.value, ...data.data];
        nextUrl.value = data.next_page_url;
    } catch (error) {
        console.error('Failed to load more members:', error);
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.title" />

        <div class="mb-10 text-center max-w-2xl mx-auto">
            <h1 class="text-4xl font-black text-slate-900 flex items-center justify-center gap-3 mb-4">
                <div class="p-2.5 bg-indigo-100 text-indigo-600 rounded-2xl">
                    <ShieldCheck :size="32" />
                </div>
                {{ translations.title }}
            </h1>
            <p class="text-slate-500 font-medium leading-relaxed">
                {{ translations.desc }}
            </p>
        </div>

        <!-- Advanced Search Bar -->
        <div class="max-w-3xl mx-auto mb-12">
            <div class="relative group">
                <div class="absolute inset-0 bg-indigo-200/20 blur-2xl rounded-full transition-all group-focus-within:bg-indigo-300/30"></div>
                <div class="relative flex items-center bg-white border border-slate-100 shadow-xl shadow-indigo-100/50 rounded-[2rem] p-2 transition-all group-focus-within:border-indigo-300 group-focus-within:ring-4 group-focus-within:ring-indigo-50">
                    <div class="pl-6 pr-3 text-slate-400">
                        <Search :size="24" />
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="translations.search_placeholder"
                        class="flex-1 py-4 bg-transparent border-none outline-none text-lg font-bold text-slate-700 placeholder:text-slate-300 placeholder:font-medium"
                    >
                    <div v-if="search" class="pr-4">
                        <button @click="search = ''" class="p-2 hover:bg-slate-50 rounded-full text-slate-300 hover:text-slate-500 transition-all">
                            <Wind :size="18" />
                        </button>
                    </div>
                </div>
                <!-- Search Tip -->
                <div class="mt-4 flex justify-center gap-4 text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <span class="flex items-center gap-1.5"><MapPin :size="12" /> {{ translations.search_city }}</span>      
                    <span class="flex items-center gap-1.5"><Users :size="12" /> {{ translations.search_name }}</span>
                    <span class="flex items-center gap-1.5"><Briefcase :size="12" /> {{ translations.search_job }}</span>       
                </div>
            </div>
        </div>

        <!-- Results Grid -->
        <div v-if="membersList.length" class="space-y-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-in fade-in slide-in-from-bottom-8 duration-700">
                <Link
                    v-for="member in membersList"
                    :key="member.memberid"
                    :href="`/wiki/member/${member.memberid}`"
                    class="group bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-indigo-100 hover:border-indigo-100 transition-all duration-500 overflow-hidden flex flex-col"        
                >
                    <div class="p-8 flex-1">
                        <div class="flex items-start justify-between mb-6">
                            <div class="w-20 h-20 rounded-[1.5rem] overflow-hidden bg-slate-50 border-4 border-white shadow-lg group-hover:scale-110 transition-transform duration-500">
                                <img v-if="member.picture" :src="member.picture" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                    <User :size="32" />
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span
                                    class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border"
                                    :class="member.life_status === 'alive' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-500 border-slate-100'"
                                >
                                    {{ translations[member.life_status] || member.life_status }}
                                </span>
                            </div>
                        </div>

                        <h3 class="text-xl font-black text-slate-900 mb-4 group-hover:text-indigo-600 transition-colors uppercase tracking-tight">
                            {{ member.name }}
                        </h3>

                        <div class="space-y-3">
                            <div v-if="member.birthplace || member.address" class="flex items-center gap-3 text-sm text-slate-500 font-medium">
                                <div class="p-1.5 bg-rose-50 text-rose-500 rounded-lg"><MapPin :size="14" /></div>
                                <span class="truncate">{{ member.birthplace || member.address }}</span>
                            </div>
                            <div v-if="member.job" class="flex items-center gap-3 text-sm text-slate-500 font-medium">
                                <div class="p-1.5 bg-blue-50 text-blue-500 rounded-lg"><Briefcase :size="14" /></div>
                                <span class="truncate">{{ member.job }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-5 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between group-hover:bg-indigo-50 transition-colors">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest group-hover:text-indigo-500">{{ translations.read_biography }}</span>
                        <ChevronRight :size="16" class="text-slate-300 group-hover:text-indigo-500 transition-all group-hover:translate-x-1" />
                    </div>
                </Link>
            </div>

            <!-- Load More Button -->
            <div v-if="nextUrl" class="flex justify-center pb-12">
                <button
                    @click="loadMore"
                    :disabled="isLoading"
                    class="group flex items-center gap-3 px-10 py-4 bg-white border border-slate-200 rounded-3xl font-black text-xs uppercase tracking-[0.2em] text-slate-500 hover:border-indigo-300 hover:text-indigo-600 hover:shadow-xl hover:shadow-indigo-100 transition-all disabled:opacity-50"
                >
                    <Loader2 v-if="isLoading" :size="18" class="animate-spin" />
                    <Plus v-else :size="18" class="group-hover:rotate-90 transition-transform duration-500" />  
                    {{ isLoading ? translations.loading : translations.load_more }}
                </button>
            </div>
        </div>

        <!-- No Results -->
        <div v-else class="py-24 text-center">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 shadow-inner">
                <Ghost :size="40" />
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">{{ translations.no_results }}</h3>
            <p class="text-slate-500">{{ translations.no_results_desc }}</p>
            <button @click="search = ''" class="mt-6 text-indigo-600 font-black uppercase tracking-widest text-xs hover:underline">
                {{ translations.clear_filters }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.page-wrapper {
    max-width: 1440px;
    margin: 0 auto;
}
</style>
