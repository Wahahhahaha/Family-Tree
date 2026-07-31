<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Settings, Layout, Save, Globe, Info, Camera } from 'lucide-vue-next';
import ImageCropper from '@/Components/ui/ImageCropper.vue';

const props = defineProps({
    system: Object,
    landing: Object,
    translations: Object,
});

const activeTab = ref('global');
const showCropModal = ref(false);
const rawImageSource = ref(null);
const activeCropField = ref(null);
const activeCropForm = ref(null);

const globalForm = useForm({
    systemname: props.system?.systemname || '',
    systemcontact: props.system?.systemcontact || '',
    systemmanager: props.system?.systemmanager || '',
    systemaddress: props.system?.systemaddress || '',
    systemlogo: null,
});

const landingForm = useForm({
    family_name: props.landing?.family_name || '',
    description: props.landing?.description || '',
    head_of_family_name: props.landing?.head_of_family_name || '',
    head_of_family_message: props.landing?.head_of_family_message || '',
    head_of_family_photo: null,
    created_by_name: props.landing?.created_by_name || '',
    created_by_photo: null,
    designed_by_name: props.landing?.designed_by_name || '',
    designed_by_photo: null,
    approved_by_name: props.landing?.approved_by_name || '',
    approved_by_photo: null,
    acknowledged_by_name: props.landing?.acknowledged_by_name || '',
    acknowledged_by_photo: null,
});

const previews = ref({
    systemlogo: props.system?.systemlogo || null,
    head_of_family_photo: props.landing?.head_of_family_photo || null,
    created_by_photo: props.landing?.created_by_photo || null,
    designed_by_photo: props.landing?.designed_by_photo || null,
    approved_by_photo: props.landing?.approved_by_photo || null,
    acknowledged_by_photo: props.landing?.acknowledged_by_photo || null,
});

const handleFileChange = (e, field, form) => {
    const file = e.target.files[0];
    if (file) {
        activeCropField.value = field;
        activeCropForm.value = form;
        rawImageSource.value = URL.createObjectURL(file);
        showCropModal.value = true;
    }
};

const onCropped = (blob) => {
    showCropModal.value = false;
    const file = new File([blob], `${activeCropField.value}.jpg`, { type: 'image/jpeg' });

    activeCropForm.value[activeCropField.value] = file;
    previews.value[activeCropField.value] = URL.createObjectURL(file);
};

const submitGlobal = () => {
    globalForm.post(route('system.global.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};

const submitLanding = () => {
    landingForm.post(route('system.landing.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.title" />

        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 flex items-center gap-3">
                <div class="p-2 bg-sky-100 text-sky-600 rounded-xl">
                    <Settings :size="24" />
                </div>
                {{ translations.head_title }}
            </h1>
            <p class="text-slate-500 mt-2 font-medium">{{ translations.desc }}</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="flex gap-2 p-1 bg-slate-100 rounded-2xl mb-8 w-fit">
            <button
                @click="activeTab = 'global'"
                class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold transition-all"
                :class="activeTab === 'global' ? 'bg-white text-sky-600 shadow-sm' : 'text-slate-500 hover:text-slate-900'"
            >
                <Globe :size="18" /> {{ translations.system_setting }}
            </button>
            <button
                @click="activeTab = 'landing'"
                class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold transition-all"
                :class="activeTab === 'landing' ? 'bg-white text-sky-600 shadow-sm' : 'text-slate-500 hover:text-slate-900'"
            >
                <Layout :size="18" /> {{ translations.landing_page }}
            </button>
        </div>

        <!-- Tab Content: Global Settings -->
        <div v-if="activeTab === 'global'" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="p-8 border-b border-slate-50">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <Info :size="20" class="text-sky-500" /> {{ translations.general_config }}
                </h3>
            </div>
            <form @submit.prevent="submitGlobal" class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Logo Upload -->
                    <div class="md:col-span-2 flex flex-col items-center justify-center p-8 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 hover:border-sky-300 transition-colors group relative">
                        <div class="w-24 h-24 rounded-2xl overflow-hidden bg-white shadow-sm border border-slate-100 mb-4 group-hover:scale-105 transition-transform">
                            <img v-if="previews.systemlogo" :src="previews.systemlogo" class="w-full h-full object-contain">
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-300">  
                                <Camera :size="32" />
                            </div>
                        </div>
                        <label class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black uppercase tracking-widest text-slate-600 cursor-pointer hover:bg-sky-50 hover:text-sky-600 transition-all">      
                            {{ translations.change_logo }}
                            <input type="file" class="hidden" @change="handleFileChange($event, 'systemlogo', globalForm)" accept="image/*">
                        </label>
                        <p class="text-[10px] text-slate-400 mt-2">{{ translations.logo_hint }}</p>     
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">{{ translations.website_name }}</label>
                        <input v-model="globalForm.systemname" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all" :placeholder="translations.placeholder_web_name">
                        <p v-if="globalForm.errors.systemname" class="text-rose-500 text-xs font-bold mt-1">{{ globalForm.errors.systemname }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">{{ translations.system_manager }}</label>
                        <input v-model="globalForm.systemmanager" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all" :placeholder="translations.placeholder_manager">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">{{ translations.contact_number }}</label>
                        <input v-model="globalForm.systemcontact" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all" :placeholder="translations.placeholder_contact">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">{{ translations.address }}</label>
                        <textarea v-model="globalForm.systemaddress" rows="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all resize-none" :placeholder="translations.placeholder_address"></textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" :disabled="globalForm.processing" class="flex items-center gap-2 px-8 py-3 bg-sky-600 text-white rounded-xl font-bold hover:bg-sky-700 shadow-lg shadow-sky-100 transition-all disabled:opacity-50">
                        <Save :size="18" /> {{ translations.save_changes }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab Content: Landing Page Settings -->
        <div v-if="activeTab === 'landing'" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="p-8 border-b border-slate-50">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <Layout :size="20" class="text-sky-500" /> {{ translations.landing_content }}
                </h3>
            </div>
            <form @submit.prevent="submitLanding" class="p-8 space-y-6">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">{{ translations.family_name }}</label>
                            <input v-model="landingForm.family_name" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all" :placeholder="translations.placeholder_family">
                            <p v-if="landingForm.errors.family_name" class="text-rose-500 text-xs font-bold mt-1">{{ landingForm.errors.family_name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">{{ translations.head_name }}</label>    
                            <input v-model="landingForm.head_of_family_name" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all" :placeholder="translations.placeholder_head">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">{{ translations.landing_desc }}</label>   
                        <textarea v-model="landingForm.description" rows="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all resize-none" :placeholder="translations.placeholder_desc"></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">{{ translations.head_message }}</label>
                        <textarea v-model="landingForm.head_of_family_message" rows="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all resize-none" :placeholder="translations.placeholder_message"></textarea>
                    </div>

                    <!-- Photo Uploads Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                        <!-- Head of Family Photo -->
                        <div class="flex flex-col items-center p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">{{ translations.head_of_family }}</span>
                            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-white shadow-sm mb-4">
                                <img v-if="previews.head_of_family_photo" :src="previews.head_of_family_photo" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300"><Camera :size="24" /></div>
                            </div>
                            <label class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase cursor-pointer hover:bg-sky-50 transition-all">
                                {{ translations.upload }}
                                <input type="file" class="hidden" @change="handleFileChange($event, 'head_of_family_photo', landingForm)" accept="image/*">
                            </label>
                        </div>

                        <!-- Created By Photo -->
                        <div class="flex flex-col items-center p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">{{ translations.created_by }}</span>
                            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-white shadow-sm mb-4">
                                <img v-if="previews.created_by_photo" :src="previews.created_by_photo" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300"><Camera :size="24" /></div>
                            </div>
                            <div class="w-full mb-3">
                                <input v-model="landingForm.created_by_name" type="text" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200" :placeholder="translations.name">
                            </div>
                            <label class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase cursor-pointer hover:bg-sky-50 transition-all">
                                {{ translations.photo }}
                                <input type="file" class="hidden" @change="handleFileChange($event, 'created_by_photo', landingForm)" accept="image/*">
                            </label>
                        </div>

                        <!-- Designed By Photo -->
                        <div class="flex flex-col items-center p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">{{ translations.designed_by }}</span>
                            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-white shadow-sm mb-4">
                                <img v-if="previews.designed_by_photo" :src="previews.designed_by_photo" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300"><Camera :size="24" /></div>
                            </div>
                            <div class="w-full mb-3">
                                <input v-model="landingForm.designed_by_name" type="text" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200" :placeholder="translations.name">
                            </div>
                            <label class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase cursor-pointer hover:bg-sky-50 transition-all">
                                {{ translations.photo }}
                                <input type="file" class="hidden" @change="handleFileChange($event, 'designed_by_photo', landingForm)" accept="image/*">
                            </label>
                        </div>

                        <!-- Approved By Photo -->
                        <div class="flex flex-col items-center p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">{{ translations.approved_by }}</span>
                            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-white shadow-sm mb-4">
                                <img v-if="previews.approved_by_photo" :src="previews.approved_by_photo" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300"><Camera :size="24" /></div>
                            </div>
                            <div class="w-full mb-3">
                                <input v-model="landingForm.approved_by_name" type="text" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200" :placeholder="translations.name">
                            </div>
                            <label class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase cursor-pointer hover:bg-sky-50 transition-all">
                                {{ translations.photo }}
                                <input type="file" class="hidden" @change="handleFileChange($event, 'approved_by_photo', landingForm)" accept="image/*">
                            </label>
                        </div>

                        <!-- Acknowledged By Photo -->
                        <div class="flex flex-col items-center p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">{{ translations.acknowledged_by }}</span>
                            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-white shadow-sm mb-4">
                                <img v-if="previews.acknowledged_by_photo" :src="previews.acknowledged_by_photo" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300"><Camera :size="24" /></div>
                            </div>
                            <div class="w-full mb-3">
                                <input v-model="landingForm.acknowledged_by_name" type="text" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200" :placeholder="translations.name">
                            </div>
                            <label class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase cursor-pointer hover:bg-sky-50 transition-all">
                                {{ translations.photo }}
                                <input type="file" class="hidden" @change="handleFileChange($event, 'acknowledged_by_photo', landingForm)" accept="image/*">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" :disabled="landingForm.processing" class="flex items-center gap-2 px-8 py-3 bg-sky-600 text-white rounded-xl font-bold hover:bg-sky-700 shadow-lg shadow-sky-100 transition-all disabled:opacity-50">
                        <Save :size="18" /> {{ translations.save_changes }}
                    </button>
                </div>
            </form>
        </div>

        <ImageCropper
            :show="showCropModal"
            :image-source="rawImageSource"
            @close="showCropModal = false"
            @cropped="onCropped"
        />
    </div>
</template>

<style scoped>
.page-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}
</style>
