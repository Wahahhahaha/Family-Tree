<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Database, Share2, Shield, UserCog, Plus, Search, Edit, Trash2, X, Globe, Save, Info } from 'lucide-vue-next';
import { useAlert } from '@/Composables/useAlert';

const props = defineProps({
    socialMedia: Array,
    levels: Array,
    roles: Array,
    translations: Object,
});

const activeTab = ref('socialmedia');
const showSocialModal = ref(false);
const isEditing = ref(false);
const selectedSocialId = ref(null);
const { showConfirm } = useAlert();

const socialForm = useForm({
    socialname: '',
    prefix: '',
    socialicon: '',
});

const openSocialModal = (social = null) => {
    if (social) {
        isEditing.value = true;
        selectedSocialId.value = social.socialid;
        socialForm.socialname = social.socialname;
        socialForm.prefix = social.prefix || '';
        socialForm.socialicon = social.socialicon || '';
    } else {
        isEditing.value = false;
        selectedSocialId.value = null;
        socialForm.reset();
    }
    socialForm.clearErrors();
    showSocialModal.value = true;
};

const submitSocial = () => {
    if (isEditing.value) {
        socialForm.put(route('master-data.social-media.update', selectedSocialId.value), {
            onSuccess: () => {
                showSocialModal.value = false;
                socialForm.reset();
            },
            preserveScroll: true,
        });
    } else {
        socialForm.post(route('master-data.social-media.store'), {
            onSuccess: () => {
                showSocialModal.value = false;
                socialForm.reset();
            },
            preserveScroll: true,
        });
    }
};

const deleteSocial = async (social) => {
    if (await showConfirm(props.translations.delete_confirm.replace(':name', social.socialname))) {
        router.delete(route('master-data.social-media.destroy', social.socialid), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.head_title" />

        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 flex items-center gap-3">
                <div class="p-2 bg-blue-100 text-blue-600 rounded-xl">
                    <Database :size="24" />
                </div>
                {{ translations.title }}
            </h1>
            <p class="text-slate-500 mt-2 font-medium">{{ translations.desc }}</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="flex flex-wrap gap-2 p-1 bg-slate-100 rounded-2xl mb-8 w-fit">
            <button
                @click="activeTab = 'socialmedia'"
                class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold transition-all"
                :class="activeTab === 'socialmedia' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-900'"
            >
                <Share2 :size="18" /> {{ translations.social_media }}
            </button>
            <button
                @click="activeTab = 'level'"
                class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold transition-all"
                :class="activeTab === 'level' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-900'"
            >
                <Shield :size="18" /> {{ translations.access_level }}
            </button>
            <button
                @click="activeTab = 'role'"
                class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold transition-all"
                :class="activeTab === 'role' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-900'"
            >
                <UserCog :size="18" /> {{ translations.account_roles }}
            </button>
        </div>

        <!-- Tab Content: Social Media (CRUD) -->
        <div v-if="activeTab === 'socialmedia'" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="flex justify-end">
                <button @click="openSocialModal()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
                    <Plus :size="18" /> {{ translations.add_social }}
                </button>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.platform }}</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.prefix_url }}</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">{{ translations.actions }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="social in socialMedia" :key="social.socialid" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center border border-slate-100">
                                            <img v-if="social.socialicon" :src="social.socialicon" class="w-6 h-6 object-contain opacity-70 group-hover:opacity-100 transition-opacity">
                                            <Globe v-else :size="18" class="text-slate-300" />
                                        </div>
                                        <span class="font-bold text-slate-700">{{ social.socialname }}</span>   
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <code class="text-xs bg-slate-50 px-2 py-1 rounded text-slate-500 font-medium">{{ social.prefix || '-' }}</code>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openSocialModal(social)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                                            <Edit :size="18" />
                                        </button>
                                        <button @click="deleteSocial(social)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all">
                                            <Trash2 :size="18" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="socialMedia.length === 0">
                                <td colspan="3" class="px-8 py-12 text-center text-slate-400 font-medium italic">
                                    {{ translations.no_social }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Content: Level (Read-Only) -->
        <div v-if="activeTab === 'level'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">       
            <div class="bg-amber-50 border border-amber-100 rounded-3xl p-6 mb-6 flex gap-4 items-center">      
                <div class="p-2 bg-white rounded-xl text-amber-500 shadow-sm shrink-0">
                    <Info :size="20" />
                </div>
                <p class="text-sm text-amber-700 font-medium leading-relaxed">{{ translations.level_protected }}</p>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.id }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.level_name }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="level in levels" :key="level.levelid">
                            <td class="px-8 py-5 font-bold text-slate-400">#{{ level.levelid }}</td>
                            <td class="px-8 py-5 font-bold text-slate-700">
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full border border-emerald-100 uppercase tracking-widest text-[10px]">
                                    {{ level.levelname }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Content: Role (Read-Only) -->
        <div v-if="activeTab === 'role'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">        
            <div class="bg-amber-50 border border-amber-100 rounded-3xl p-6 mb-6 flex gap-4 items-center">      
                <div class="p-2 bg-white rounded-xl text-amber-500 shadow-sm shrink-0">
                    <Info :size="20" />
                </div>
                <p class="text-sm text-amber-700 font-medium leading-relaxed">{{ translations.role_protected }}</p>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.id }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ translations.role_name }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="role in roles" :key="role.roleid">
                            <td class="px-8 py-5 font-bold text-slate-400">#{{ role.roleid }}</td>
                            <td class="px-8 py-5 font-bold text-slate-700">
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full border border-indigo-100 uppercase tracking-widest text-[10px]">
                                    {{ role.rolename }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Social Media Modal (Add/Edit) -->
        <div v-if="showSocialModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">  
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showSocialModal = false"></div>

            <div class="relative bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">     
                    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-3">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-xl">
                            <Share2 :size="20" />
                        </div>
                        {{ isEditing ? translations.edit_platform : translations.add_platform }}
                    </h3>
                    <button @click="showSocialModal = false" class="p-2 hover:bg-slate-200 rounded-full transition-colors">
                        <X :size="20" class="text-slate-400" />
                    </button>
                </div>

                <form @submit.prevent="submitSocial" class="p-8 space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">{{ translations.platform_name }}</label>
                        <input v-model="socialForm.socialname" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none transition-all font-medium" :placeholder="translations.platform_name" required>
                        <p v-if="socialForm.errors.socialname" class="text-rose-500 text-xs font-bold mt-1">{{ socialForm.errors.socialname }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">{{ translations.url_prefix }}</label>
                        <input v-model="socialForm.prefix" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none transition-all font-medium text-blue-600" :placeholder="translations.url_prefix">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">{{ translations.icon_url }}</label>
                        <input v-model="socialForm.socialicon" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none transition-all font-medium" :placeholder="translations.icon_url">
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showSocialModal = false" class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
                            {{ translations.cancel }}
                        </button>
                        <button type="submit" :disabled="socialForm.processing" class="flex items-center gap-2 px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all disabled:opacity-50">
                            <Save :size="18" /> {{ isEditing ? translations.update_platform : translations.save_platform }}
                        </button>
                    </div>
                </form>
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
