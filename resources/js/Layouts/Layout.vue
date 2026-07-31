<script setup>
import { Link, Head, usePage } from '@inertiajs/vue3';
import { 
    House, Images, CalendarCheck, Users, 
    User, LogOut, ChevronDown, Menu, X, 
    Bell, Search, Settings, ShieldCheck,
    LayoutDashboard, Activity, Key, Database,
    UserCog, Download, MapPin, Calendar, Mail,
    Landmark, UserCheck, Trash2
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Footer from '@/Components/ui/Footer.vue';
import GlobalAlert from '@/Components/ui/GlobalAlert.vue';
import Chatbot from '@/Components/Chatbot.vue';
import LanguageToggle from '@/Components/LanguageToggle.vue';

const page = usePage();
const systemname = computed(() => page.props.systemname);
const systemlogo = computed(() => page.props.systemlogo);
const auth = computed(() => page.props.auth);
const user = computed(() => auth.value?.user);

const showFamilyDropdown = ref(false);
const showManagementDropdown = ref(false);
const showProfileDropdown = ref(false);
const showMobileMenu = ref(false);

const toggleDropdown = (type) => {
    if (type === 'family') {
        showFamilyDropdown.value = !showFamilyDropdown.value;
        showManagementDropdown.value = false;
        showProfileDropdown.value = false;
    } else if (type === 'management') {
        showManagementDropdown.value = !showManagementDropdown.value;
        showFamilyDropdown.value = false;
        showProfileDropdown.value = false;
    } else if (type === 'profile') {
        showProfileDropdown.value = !showProfileDropdown.value;
        showFamilyDropdown.value = false;
        showManagementDropdown.value = false;
    } else if (type === 'mobile') {
        showMobileMenu.value = !showMobileMenu.value;
        if(showMobileMenu.value) {
            showFamilyDropdown.value = false;
            showManagementDropdown.value = false;
            showProfileDropdown.value = false;
        }
    }
};

const closeAllDropdowns = (e) => {
    if (e && (e.target.closest('.mobile-menu-container') || e.target.closest('.mobile-toggle-btn'))) return;
    showFamilyDropdown.value = false;
    showManagementDropdown.value = false;
    showProfileDropdown.value = false;
    showMobileMenu.value = false;
};

const can = (slug) => auth.value?.permissions?.includes(slug);

const hasFamilyAccess = computed(() => {
    return can('menu_tree') || can('menu_calendar') || can('menu_events') ||
           can('menu_wiki') || can('menu_gallery') || can('menu_inheritance') ||
           can('menu_letters') || can('menu_location') || can('menu_validation');
});

const hasManagementAccess = computed(() => {
    return can('menu_activity') || can('menu_backup') || can('menu_master') ||
           can('menu_permissions') || can('menu_recycle') || can('menu_system') ||
           can('menu_users');
});

onMounted(() => { window.addEventListener('click', closeAllDropdowns); });
onUnmounted(() => { window.removeEventListener('click', closeAllDropdowns); });
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] font-sora">
        <nav class="sticky top-6 z-50 bg-white/80 backdrop-blur-xl border border-slate-100 shadow-xl max-w-[1400px] mx-auto px-4 sm:px-8 rounded-2xl mt-6">
            <div class="flex justify-between items-center h-20">
                    <div class="flex items-center gap-8">
                        <Link :href="route('home')" class="flex items-center gap-3 group">
                            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200 group-hover:scale-110 transition-all duration-500 overflow-hidden text-white shrink-0"> 
                                <img v-if="systemlogo" :src="systemlogo" class="w-full h-full object-cover">    
                                <Users v-else :size="24" />
                            </div>
                            <span class="text-lg font-black text-slate-900 tracking-tight uppercase hidden sm:block">{{ systemname }}</span>
                        </Link>

                        <div class="hidden lg:flex items-center gap-1">
                            <Link v-if="can('menu_home')" :href="route('home')" class="nav-link" :class="{ 'active': $page.url === '/' }">
                                <LayoutDashboard :size="18" /> {{ __('home') }}
                            </Link>

                            <div v-if="hasFamilyAccess" class="relative">
                                <button @click.stop="toggleDropdown('family')"
                                    class="px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-all"
                                    :class="showFamilyDropdown ? 'bg-white text-sky-600 shadow-sm' : 'text-slate-500 hover:text-slate-900 hover:bg-white/50'">
                                    {{ __('family') }}
                                    <ChevronDown :size="14" class="transition-transform duration-300" :class="{ 'rotate-180': showFamilyDropdown }" />
                                </button>

                                <div v-if="showFamilyDropdown" class="dropdown-panel animate-in fade-in slide-in-from-top-2 duration-300">
                                    <Link v-if="can('menu_tree')" :href="route('home')" class="dropdown-item">  
                                        <House :size="18" class="text-sky-500" /> {{ __('tree_view') }}
                                    </Link>
                                    <Link v-if="can('menu_calendar')" :href="route('calendar.index')" class="dropdown-item">
                                        <Calendar :size="18" class="text-sky-500" /> {{ __('calendar') }}       
                                    </Link>
                                    <Link v-if="can('menu_events')" :href="route('events.index')" class="dropdown-item">
                                        <CalendarCheck :size="18" class="text-emerald-500" /> {{ __('events') }}
                                    </Link>
                                    <Link v-if="can('menu_wiki')" :href="route('wiki.index')" class="dropdown-item">
                                        <ShieldCheck :size="18" class="text-indigo-500" /> {{ __('wiki') }}
                                    </Link>
                                    <Link v-if="can('menu_gallery')" :href="route('gallery.index')" class="dropdown-item">
                                        <Images :size="18" class="text-sky-500" /> {{ __('gallery') }}
                                    </Link>
                                    <Link v-if="can('menu_inheritance')" :href="route('inheritance.index')" class="dropdown-item">
                                        <Landmark :size="18" class="text-amber-600" /> {{ __('inheritance') }}  
                                    </Link>
                                    <Link v-if="can('menu_letters')" :href="route('letters.index')" class="dropdown-item">
                                        <Mail :size="18" class="text-amber-500" /> {{ __('letters') }}
                                    </Link>
                                    <Link v-if="can('menu_location')" :href="route('live-location.index')" class="dropdown-item">
                                        <MapPin :size="18" class="text-rose-500" /> {{ __('live_location') }}   
                                    </Link>
                                    <Link v-if="can('menu_validation')" :href="route('validation.index')" class="dropdown-item">
                                        <UserCheck :size="18" class="text-emerald-500" /> {{ __('validation') }}
                                    </Link>
                                </div>
                            </div>

                            <div v-if="hasManagementAccess" class="relative">
                                <button @click.stop="toggleDropdown('management')"
                                    class="px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-all"
                                    :class="showManagementDropdown ? 'bg-white text-sky-600 shadow-sm' : 'text-slate-500 hover:text-slate-900 hover:bg-white/50'">
                                    {{ __('management') }}
                                    <ChevronDown :size="14" class="transition-transform duration-300" :class="{ 'rotate-180': showManagementDropdown }" />
                                </button>

                                <div v-if="showManagementDropdown" class="dropdown-panel animate-in fade-in slide-in-from-top-2 duration-300">
                                    <Link v-if="can('menu_activity')" :href="route('activity-log.index')" class="dropdown-item">
                                        <Activity :size="18" class="text-rose-400" /> {{ __('activity_log') }}  
                                    </Link>
                                    <Link v-if="can('menu_backup')" :href="route('backup.index')" class="dropdown-item">
                                        <Download :size="18" class="text-emerald-400" /> {{ __('backup') }}     
                                    </Link>
                                    <Link v-if="can('menu_master')" :href="route('master-data.index')" class="dropdown-item">
                                        <Database :size="18" class="text-blue-400" /> {{ __('master_data') }}   
                                    </Link>
                                    <Link v-if="can('menu_permissions')" :href="route('permissions.index')" class="dropdown-item">
                                        <ShieldCheck :size="18" class="text-indigo-400" /> {{ __('permissions') }}
                                    </Link>
                                    <Link v-if="can('menu_recycle')" :href="route('recycle-bin.index')" class="dropdown-item">
                                        <Trash2 :size="18" class="text-amber-500" /> {{ __('recycle_bin') }}    
                                    </Link>
                                    <Link v-if="can('menu_system')" :href="route('system.index')" class="dropdown-item">
                                        <Settings :size="18" class="text-slate-400" /> {{ __('system') }}       
                                    </Link>
                                    <Link v-if="can('menu_users')" :href="route('users.index')" class="dropdown-item">
                                        <UserCog :size="18" class="text-indigo-400" /> {{ __('user_data') }}    
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-4">
                        <div class="hidden sm:block">
                            <LanguageToggle />
                        </div>

                        <div class="relative hidden sm:block">
                            <button @click.stop="toggleDropdown('profile')" class="user-profile-btn">
                                <div class="text-right hidden lg:block">
                                    <strong class="block text-[13px] text-slate-900 leading-tight">{{ user?.username || 'Guest' }}</strong>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ auth?.is_family_member ? __('family_member') : __('staff') }}</span>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-slate-100 border-2 border-white shadow-sm flex items-center justify-center overflow-hidden shrink-0">
                                    <User :size="20" class="text-slate-400" />
                                </div>
                            </button>

                            <div v-if="showProfileDropdown" class="dropdown-panel animate-in fade-in slide-in-from-top-2 duration-300 right-0 mt-2 w-56">
                                <div class="px-4 py-3 lg:hidden border-b border-slate-50 mb-2">
                                    <strong class="block text-[13px] text-slate-900 leading-tight">{{ user?.username || 'Guest' }}</strong>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ auth?.is_family_member ? __('family_member') : __('staff') }}</span>
                                </div>
                                <Link :href="route('profile.index')" class="dropdown-item">
                                    <UserCog :size="18" class="text-slate-400" /> {{ __('account_settings') }}  
                                </Link>
                                <div class="h-px bg-slate-50 my-2"></div>
                                <Link :href="route('logout')" method="post" as="button" class="dropdown-item text-rose-500 w-full text-left">
                                    <LogOut :size="18" /> {{ __('sign_out') }}
                                </Link>
                            </div>
                        </div>

                        <button @click.stop="toggleDropdown('mobile')" class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors mobile-toggle-btn">
                            <Menu v-if="!showMobileMenu" :size="24" />
                            <X v-else :size="24" />
                        </button>
                    </div>
                </div>

                <div v-if="showMobileMenu" class="lg:hidden absolute top-full left-0 right-0 mt-4 bg-white rounded-2xl border border-slate-100 shadow-2xl p-4 animate-in fade-in slide-in-from-top-4 duration-300 z-50 mobile-menu-container">
                    <div class="flex items-center justify-between p-4 mb-4 bg-slate-50 rounded-xl sm:hidden">   
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                                <User :size="20" class="text-slate-400" />
                            </div>
                            <div>
                                <strong class="block text-sm text-slate-900">{{ user?.username || 'Guest' }}</strong>
                                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ auth?.is_family_member ? __('family_member') : __('staff') }}</span>
                            </div>
                        </div>
                        <LanguageToggle :is-mobile="true" />
                    </div>

                    <div class="space-y-1 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                        <Link v-if="can('menu_home')" :href="route('home')" class="mobile-nav-link" :class="{ 'active': $page.url === '/' }">
                            <LayoutDashboard :size="18" /> {{ __('home') }}
                        </Link>

                        <div v-if="hasFamilyAccess" class="pt-4 pb-2">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 mb-2">{{ __('family') }}</h4>
                            <div class="space-y-1">
                                <Link v-if="can('menu_tree')" :href="route('home')" class="mobile-nav-link">    
                                    <House :size="18" class="text-sky-500" /> {{ __('tree_view') }}
                                </Link>
                                <Link v-if="can('menu_calendar')" :href="route('calendar.index')" class="mobile-nav-link">
                                    <Calendar :size="18" class="text-sky-500" /> {{ __('calendar') }}
                                </Link>
                                <Link v-if="can('menu_events')" :href="route('events.index')" class="mobile-nav-link">
                                    <CalendarCheck :size="18" class="text-emerald-500" /> {{ __('events') }}    
                                </Link>
                                <Link v-if="can('menu_wiki')" :href="route('wiki.index')" class="mobile-nav-link">
                                    <ShieldCheck :size="18" class="text-indigo-500" /> {{ __('wiki') }}  
                                </Link>
                                <Link v-if="can('menu_gallery')" :href="route('gallery.index')" class="mobile-nav-link">
                                    <Images :size="18" class="text-sky-500" /> {{ __('gallery') }}
                                </Link>
                                <Link v-if="can('menu_inheritance')" :href="route('inheritance.index')" class="mobile-nav-link">
                                    <Landmark :size="18" class="text-amber-600" /> {{ __('inheritance') }}      
                                </Link>
                                <Link v-if="can('menu_letters')" :href="route('letters.index')" class="mobile-nav-link">
                                    <Mail :size="18" class="text-amber-500" /> {{ __('letters') }}
                                </Link>
                                <Link v-if="can('menu_location')" :href="route('live-location.index')" class="mobile-nav-link">
                                    <MapPin :size="18" class="text-rose-500" /> {{ __('live_location') }}       
                                </Link>
                                <Link v-if="can('menu_validation')" :href="route('validation.index')" class="mobile-nav-link">
                                    <UserCheck :size="18" class="text-emerald-500" /> {{ __('validation') }}    
                                </Link>
                            </div>
                        </div>

                        <div v-if="hasManagementAccess" class="pt-4 pb-2 border-t border-slate-50">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 mb-2">{{ __('management') }}</h4>
                            <div class="space-y-1">
                                <Link v-if="can('menu_activity')" :href="route('activity-log.index')" class="mobile-nav-link">
                                    <Activity :size="18" class="text-rose-400" /> {{ __('activity_log') }}      
                                </Link>
                                <Link v-if="can('menu_backup')" :href="route('backup.index')" class="mobile-nav-link">
                                    <Download :size="18" class="text-emerald-400" /> {{ __('backup') }}
                                </Link>
                                <Link v-if="can('menu_master')" :href="route('master-data.index')" class="mobile-nav-link">
                                    <Database :size="18" class="text-blue-400" /> {{ __('master_data') }}       
                                </Link>
                                <Link v-if="can('menu_permissions')" :href="route('permissions.index')" class="mobile-nav-link">
                                    <ShieldCheck :size="18" class="text-indigo-400" /> {{ __('permissions') }}  
                                </Link>
                                <Link v-if="can('menu_recycle')" :href="route('recycle-bin.index')" class="mobile-nav-link">
                                    <Trash2 :size="18" class="text-amber-500" /> {{ __('recycle_bin') }}        
                                </Link>
                                <Link v-if="can('menu_system')" :href="route('system.index')" class="mobile-nav-link">
                                    <Settings :size="18" class="text-slate-400" /> {{ __('system') }}
                                </Link>
                                <Link v-if="can('menu_users')" :href="route('users.index')" class="mobile-nav-link">
                                    <UserCog :size="18" class="text-indigo-400" /> {{ __('user_data') }}        
                                </Link>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-50 sm:hidden">
                            <Link :href="route('profile.index')" class="mobile-nav-link">
                                <UserCog :size="18" class="text-slate-400" /> {{ __('account_settings') }}      
                            </Link>
                            <Link :href="route('logout')" method="post" as="button" class="mobile-nav-link text-rose-500 w-full text-left">
                                <LogOut :size="18" /> {{ __('sign_out') }}
                            </Link>
                        </div>
                    </div>
                </div>
        </nav>

        <main class="page-content">
            <transition name="fade" mode="out-in">
                <slot />
            </transition>
        </main>

        <GlobalAlert />
        <Chatbot v-if="user" />
        <Footer :systemname="systemname" />
    </div>
</template>

<style scoped>
@reference "../../css/app.css";

.nav-link {
    @apply px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 flex items-center gap-2 transition-all hover:text-slate-900 hover:bg-white/50;
}
.nav-link.active {
    @apply bg-white text-indigo-600 shadow-sm;
}
.mobile-nav-link {
    @apply flex items-center gap-3 px-4 py-3.5 rounded-xl text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 hover:text-indigo-600;
}
.mobile-nav-link.active {
    @apply bg-indigo-50 text-indigo-600;
}
.dropdown-panel {
    @apply absolute left-0 mt-2 w-64 bg-white rounded-2xl border border-slate-100 shadow-xl p-2 z-[60];
}
.dropdown-item {
    @apply flex items-center gap-3 px-4 py-3 rounded-xl text-[13px] font-bold text-slate-600 transition-all hover:bg-slate-50 hover:text-indigo-600;
}
.user-profile-btn {
    @apply flex items-center gap-4 px-3 py-2 rounded-2xl transition-all hover:bg-white;
}
.page-content {
    min-height: calc(100vh - 160px);
}
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    @apply bg-slate-200 rounded-full;
}
</style>
