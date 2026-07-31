<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { User, Shield, Lock, Save, Mail, Phone, AtSign, CheckCircle2, Globe, Plus, Trash2, MapPin, Camera, Loader2 } from 'lucide-vue-next';
import ImageCropper from '@/Components/ui/ImageCropper.vue';

const props = defineProps({
    user: Object,
    employer: Object,
    familyMember: Object,
    socialMediaOptions: Array,
    translations: Object,
});

const activeTab = ref('account');
const showCropModal = ref(false);
const rawImageSource = ref(null);
const authData = usePage().props.auth;

const employerForm = useForm({
    username: props.user.username,
    name: props.employer?.name || '',
    email: props.employer?.email || '',
    phonenumber: props.employer?.phonenumber || '',
});

const familyMemberForm = useForm({
    name: props.familyMember?.name || '',
    email: props.familyMember?.email || '',
    phonenumber: props.familyMember?.phonenumber || '',
    gender: props.familyMember?.gender || 'male',
    birthdate: props.familyMember?.birthdate || '',
    birthplace: props.familyMember?.birthplace || '',
    bloodtype: props.familyMember?.bloodtype || '',
    education_status: props.familyMember?.education_status || '',
    address_country: props.familyMember?.address?.country || 'Indonesia',
    address_province: props.familyMember?.address?.province || '',
    address_city: props.familyMember?.address?.city || '',
    address_detail: props.familyMember?.address?.detail || '',
    job: props.familyMember?.job || '',
    social_media: props.familyMember?.social_media || [],
    photo: null,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const localPhotoPreview = ref(null);

const handlePictureSelect = (e) => {
    const file = e.target.files[0];
    if (file) {
        rawImageSource.value = URL.createObjectURL(file);
        showCropModal.value = true;
    }
};

const onCropped = (blob) => {
    showCropModal.value = false;
    const file = new File([blob], 'profile.jpg', { type: 'image/jpeg' });
    localPhotoPreview.value = URL.createObjectURL(file);
    familyMemberForm.photo = file;
};

const allCountries = ["Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia", "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo", "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Ivory Coast", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kosovo", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau", "Palestine", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"];

const detailedLocations = {
    'Indonesia': {
        'Aceh': ['Banda Aceh', 'Lhokseumawe', 'Langsa', 'Meulaboh'],
        'Bali': ['Denpasar', 'Badung', 'Gianyar', 'Tabanan', 'Klungkung'],
        'Banten': ['Serang', 'Tangerang', 'Cilegon', 'Tangerang Selatan'],
        'Bengkulu': ['Bengkulu', 'Curup', 'Manna'],
        'DI Yogyakarta': ['Yogyakarta', 'Sleman', 'Bantul', 'Kulon Progo', 'Gunung Kidul'],
        'DKI Jakarta': ['Jakarta Pusat', 'Jakarta Selatan', 'Jakarta Utara', 'Jakarta Timur', 'Jakarta Barat'], 
        'Gorontalo': ['Gorontalo', 'Limboto'],
        'Jambi': ['Jambi', 'Sungai Penuh'],
        'Jawa Barat': ['Bandung', 'Bogor', 'Bekasi', 'Depok', 'Cianjur', 'Sukabumi', 'Cirebon', 'Tasikmalaya', 'Garut'],
        'Jawa Tengah': ['Semarang', 'Surakarta', 'Magelang', 'Banyumas', 'Tegal', 'Pekalongan', 'Salatiga', 'Cilacap'],
        'Jawa Timur': ['Surabaya', 'Malang', 'Sidoarjo', 'Gresik', 'Kediri', 'Pasuruan', 'Bojonegoro', 'Banyuwangi', 'Madiun'],
        'Kalimantan Barat': ['Pontianak', 'Singkawang'],
        'Kalimantan Selatan': ['Banjarmasin', 'Banjarbaru'],
        'Kalimantan Tengah': ['Palangkaraya'],
        'Kalimantan Timur': ['Samarinda', 'Balikpapan', 'Bontang'],
        'Kalimantan Utara': ['Tanjung Selor', 'Tarakan'],
        'Kepulauan Bangka Belitung': ['Pangkal Pinang'],
        'Kepulauan Riau': ['Tanjung Pinang', 'Batam'],
        'Lampung': ['Bandar Lampung', 'Metro'],
        'Maluku': ['Ambon', 'Tual'],
        'Maluku Utara': ['Sofifi', 'Ternate'],
        'Nusa Tenggara Barat': ['Mataram', 'Bima'],
        'Nusa Tenggara Timur': ['Kupang'],
        'Papua': ['Jayapura'],
        'Papua Barat': ['Manokwari', 'Sorong'],
        'Riau': ['Pekanbaru', 'Dumai'],
        'Sulawesi Barat': ['Mamuju'],
        'Sulawesi Selatan': ['Makassar', 'Parepare', 'Palopo'],
        'Sulawesi Tengah': ['Palu'],
        'Sulawesi Tenggara': ['Kendari', 'Bau-Bau'],
        'Sulawesi Utara': ['Manado', 'Bitung', 'Tomohon'],
        'Sumatera Barat': ['Padang', 'Bukittinggi', 'Solok', 'Payakumbuh', 'Padang Panjang', 'Pariaman', 'Sawahlunto'],
        'Sumatera Selatan': ['Palembang', 'Prabumulih', 'Lubuklinggau'],
        'Sumatera Utara': ['Medan', 'Binjai', 'Pematangsiantar', 'Sibolga', 'Tanjungbalai'],
    }
};

const hasDetailedProvinces = computed(() => familyMemberForm.address_country && detailedLocations[familyMemberForm.address_country]);
const provinces = computed(() => hasDetailedProvinces.value ? Object.keys(detailedLocations[familyMemberForm.address_country]) : []);  
const cities = computed(() => (hasDetailedProvinces.value && familyMemberForm.address_province) ? (detailedLocations[familyMemberForm.address_country][familyMemberForm.address_province] || []) : []);

watch(() => familyMemberForm.address_country, () => { familyMemberForm.address_province = ''; familyMemberForm.address_city = ''; });
watch(() => familyMemberForm.address_province, () => { familyMemberForm.address_city = ''; });

const addSocialMedia = () => {
    familyMemberForm.social_media.push({
        socialid: props.socialMediaOptions[0]?.socialid || null,
        link: '',
    });
};

const removeSocialMedia = (index) => { familyMemberForm.social_media.splice(index, 1); };
const updateEmployer = () => { employerForm.post(route('profile.employer.update'), { preserveScroll: true, }); };
const updateFamilyMember = () => {
    familyMemberForm.post(route('profile.family.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            localPhotoPreview.value = null;
            familyMemberForm.photo = null;
        }
    });
};

const updatePassword = () => {
    passwordForm.post(route('profile.password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.title" />

        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 flex items-center gap-3">
                <div class="p-2 bg-slate-100 text-slate-600 rounded-xl">
                    <User :size="24" />
                </div>
                {{ translations.title }}
            </h1>
            <p class="text-slate-500 mt-2 font-medium">{{ translations.desc }}</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-64 shrink-0 space-y-6">
                <div v-if="familyMember" class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8 flex flex-col items-center">
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-[2.5rem] overflow-hidden border-4 border-white shadow-xl bg-slate-50">
                            <img v-if="localPhotoPreview || authData.user.picture" :src="localPhotoPreview || authData.user.picture" class="w-full h-full object-cover">
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-300">  
                                <User :size="48" />
                            </div>
                        </div>
                        <label class="absolute -bottom-2 -right-2 p-3 bg-indigo-600 text-white rounded-2xl shadow-lg cursor-pointer hover:bg-indigo-700 transition-all active:scale-90 group-hover:scale-110">
                            <Camera :size="18" />
                            <input type="file" class="hidden" @change="handlePictureSelect" accept="image/*">   
                        </label>
                    </div>
                    <div v-if="localPhotoPreview" class="mt-4 px-3 py-1 bg-amber-50 border border-amber-100 rounded-full animate-in fade-in zoom-in duration-300">
                         <p class="text-[9px] text-amber-600 font-black uppercase tracking-widest">{{ translations.unsaved_preview }}</p>
                    </div>
                    <h3 class="mt-6 text-sm font-black text-slate-900 uppercase tracking-widest text-center">{{ familyMember.name }}</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ translations.family_member }}</p>
                </div>

                <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-2 flex flex-col gap-1"> 
                    <button
                        @click="activeTab = 'account'"
                        class="flex items-center gap-3 px-5 py-3 rounded-2xl text-sm font-bold transition-all"  
                        :class="activeTab === 'account' ? 'bg-sky-50 text-sky-600' : 'text-slate-500 hover:bg-slate-50'"
                    >
                        <User :size="18" /> {{ translations.title }}
                    </button>
                    <button
                        @click="activeTab = 'security'"
                        class="flex items-center gap-3 px-5 py-3 rounded-2xl text-sm font-bold transition-all"  
                        :class="activeTab === 'security' ? 'bg-rose-50 text-rose-600' : 'text-slate-500 hover:bg-slate-50'"
                    >
                        <Lock :size="18" /> {{ translations.security }}
                    </button>
                </div>
            </div>

            <div class="flex-1 max-w-3xl space-y-8">
                <div v-if="activeTab === 'account'" class="animate-in fade-in slide-in-from-right-4 duration-500 space-y-8">
                    <div v-if="employer" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-8 border-b border-slate-50">
                            <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                <Shield :size="20" class="text-sky-500" /> {{ translations.employer_profile }}
                            </h3>
                            <p class="text-slate-500 text-xs font-bold mt-1">{{ translations.employer_desc }}</p>
                        </div>
                        <form @submit.prevent="updateEmployer" class="p-8 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.username }}</label>
                                    <div class="relative">
                                        <AtSign class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                                        <input v-model="employerForm.username" type="text" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all font-bold text-sm">
                                    </div>
                                    <p v-if="employerForm.errors.username" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ employerForm.errors.username }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.full_name }}</label>
                                    <div class="relative">
                                        <Shield class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                                        <input v-model="employerForm.name" type="text" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all font-bold text-sm">
                                    </div>
                                    <p v-if="employerForm.errors.name" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ employerForm.errors.name }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.email_address }}</label>
                                    <div class="relative">
                                        <Mail class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                                        <input v-model="employerForm.email" type="email" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all font-bold text-sm">
                                    </div>
                                    <p v-if="employerForm.errors.email" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ employerForm.errors.email }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.phone_number }}</label>
                                    <div class="relative">
                                        <Phone class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                                        <input v-model="employerForm.phonenumber" type="text" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all font-bold text-sm">
                                    </div>
                                    <p v-if="employerForm.errors.phonenumber" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ employerForm.errors.phonenumber }}</p>
                                </div>
                            </div>
                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="employerForm.processing" class="flex items-center gap-2 px-8 py-3 bg-sky-600 text-white rounded-xl font-bold hover:bg-sky-700 shadow-lg shadow-sky-100 transition-all disabled:opacity-50">
                                    <Save :size="18" /> {{ translations.save_employer }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <div v-if="familyMember" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-8 border-b border-slate-50">
                            <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                <User :size="20" class="text-indigo-500" /> {{ translations.family_profile }}
                            </h3>
                            <p class="text-slate-500 text-xs font-bold mt-1">{{ translations.family_desc }}</p>
                        </div>
                        <form @submit.prevent="updateFamilyMember" class="p-8 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.full_name }}</label>
                                    <input v-model="familyMemberForm.name" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm">
                                    <p v-if="familyMemberForm.errors.name" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.name }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.email_address }}</label>
                                    <input v-model="familyMemberForm.email" type="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm">
                                    <p v-if="familyMemberForm.errors.email" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.email }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.phone_number }}</label>
                                    <input v-model="familyMemberForm.phonenumber" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm">
                                    <p v-if="familyMemberForm.errors.phonenumber" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.phonenumber }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.gender }}</label>
                                    <select v-model="familyMemberForm.gender" disabled class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 outline-none transition-all font-bold text-sm appearance-none cursor-not-allowed">
                                        <option value="male">{{ translations.male }}</option>
                                        <option value="female">{{ translations.female }}</option>
                                    </select>
                                    <p v-if="familyMemberForm.errors.gender" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.gender }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.birth_date }}</label>
                                    <input v-model="familyMemberForm.birthdate" type="date" disabled class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 outline-none transition-all font-bold text-sm cursor-not-allowed">
                                    <p v-if="familyMemberForm.errors.birthdate" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.birthdate }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.birth_place }}</label>
                                    <input v-model="familyMemberForm.birthplace" type="text" disabled class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 outline-none transition-all font-bold text-sm cursor-not-allowed">
                                    <p v-if="familyMemberForm.errors.birthplace" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.birthplace }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.blood_type }}</label>
                                    <select v-model="familyMemberForm.bloodtype" :disabled="props.familyMember?.bloodtype !== null && props.familyMember?.bloodtype !== ''" :class="props.familyMember?.bloodtype !== null && props.familyMember?.bloodtype !== '' ? 'bg-slate-50 text-slate-500 cursor-not-allowed' : 'bg-white'" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm appearance-none">
                                        <option value="">{{ translations.select_blood }}</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="AB">AB</option>
                                        <option value="O">O</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                    <p v-if="familyMemberForm.errors.bloodtype" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.bloodtype }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.education }}</label>
                                    <input v-model="familyMemberForm.education_status" type="text" :placeholder="translations.edu_placeholder" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm">
                                    <p v-if="familyMemberForm.errors.education_status" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.education_status }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.job }}</label>
                                    <input v-model="familyMemberForm.job" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm">
                                    <p v-if="familyMemberForm.errors.job" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.job }}</p>
                                </div>
                                <div class="md:col-span-2 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.country }}</label>
                                            <select v-model="familyMemberForm.address_country" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm appearance-none bg-white">
                                                <option v-for="country in allCountries" :key="country" :value="country">{{ country }}</option>
                                            </select>
                                            <p v-if="familyMemberForm.errors.address_country" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.address_country }}</p>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.province }}</label>
                                            <div v-if="hasDetailedProvinces">
                                                <select v-model="familyMemberForm.address_province" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm appearance-none bg-white">
                                                    <option value="">{{ translations.select_province }}</option>
                                                    <option v-for="province in provinces" :key="province" :value="province">{{ province }}</option>
                                                </select>
                                            </div>
                                            <div v-else>
                                                <input v-model="familyMemberForm.address_province" type="text" :placeholder="translations.enter_province" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm">
                                            </div>
                                            <p v-if="familyMemberForm.errors.address_province" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.address_province }}</p>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.city_region }}</label>
                                            <div v-if="hasDetailedProvinces">
                                                <select v-model="familyMemberForm.address_city" :disabled="!familyMemberForm.address_province" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm appearance-none bg-white disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed">
                                                    <option value="">{{ translations.select_city }}</option>
                                                    <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                                                </select>
                                            </div>
                                            <div v-else>
                                                <input v-model="familyMemberForm.address_city" type="text" :placeholder="translations.enter_city" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm">
                                            </div>
                                            <p v-if="familyMemberForm.errors.address_city" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.address_city }}</p>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.detail_address }}</label>
                                        <textarea v-model="familyMemberForm.address_detail" rows="2" :placeholder="translations.address_placeholder" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm"></textarea>
                                        <p v-if="familyMemberForm.errors.address_detail" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ familyMemberForm.errors.address_detail }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-50 space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-black text-slate-900 flex items-center gap-2">      
                                        <Globe :size="16" class="text-indigo-500" /> {{ translations.social_links }}
                                    </h4>
                                    <button type="button" @click="addSocialMedia" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 transition-colors">
                                        <Plus :size="14" /> {{ translations.add_social }}
                                    </button>
                                </div>

                                <div v-if="familyMemberForm.social_media.length > 0" class="space-y-3">
                                    <div v-for="(social, index) in familyMemberForm.social_media" :key="index" class="flex gap-3 animate-in fade-in slide-in-from-top-2 duration-300">
                                        <div class="w-1/3">
                                            <select v-model="social.socialid" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm appearance-none bg-white">
                                                <option v-for="option in socialMediaOptions" :key="option.socialid" :value="option.socialid">
                                                    {{ option.socialname }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="flex-1 relative">
                                            <input v-model="social.link" type="text" :placeholder="translations.social_placeholder" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-bold text-sm">
                                            <button type="button" @click="removeSocialMedia(index)" class="absolute right-3 top-1/2 -translate-y-1/2 text-rose-400 hover:text-rose-600 transition-colors p-1">
                                                <Trash2 :size="16" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                    <p class="text-slate-400 text-xs font-bold italic">{{ translations.no_social }}</p>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="familyMemberForm.processing" class="flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all disabled:opacity-50">
                                    <Save :size="18" /> {{ translations.save_family }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div v-if="activeTab === 'security'" class="animate-in fade-in slide-in-from-right-4 duration-500">
                    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">   
                        <div class="p-8 border-b border-slate-50">
                            <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                <Lock :size="20" class="text-rose-500" /> {{ translations.change_password }}
                            </h3>
                        </div>
                        <form @submit.prevent="updatePassword" class="p-8 space-y-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.current_password }}</label>
                                <input v-model="passwordForm.current_password" type="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-50 outline-none transition-all font-bold text-sm">
                                <p v-if="passwordForm.errors.current_password" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ passwordForm.errors.current_password }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.new_password }}</label>
                                <input v-model="passwordForm.password" type="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-50 outline-none transition-all font-bold text-sm">
                                <p v-if="passwordForm.errors.password" class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ passwordForm.errors.password }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">{{ translations.confirm_password }}</label>
                                <input v-model="passwordForm.password_confirmation" type="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-50 outline-none transition-all font-bold text-sm">
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="passwordForm.processing" class="flex items-center gap-2 px-8 py-3 bg-rose-600 text-white rounded-xl font-bold hover:bg-rose-700 shadow-lg shadow-indigo-100 transition-all disabled:opacity-50">
                                    <CheckCircle2 :size="18" /> {{ translations.update_password }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
@reference "../../../css/app.css";

.page-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}
</style>
