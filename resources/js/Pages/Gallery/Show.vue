<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import ImageCropper from '@/Components/ui/ImageCropper.vue';
import {
    ChevronLeft, Plus, Image as ImageIcon,
    Upload, X, Trash2, Calendar, User,
    Maximize2, Download, Loader2, Save, RotateCw,
    FileText, Type
} from 'lucide-vue-next';
import { ref } from 'vue';
import imageCompression from 'browser-image-compression';

const props = defineProps({
    album: Object,
    translations: Object,
});

const showUploadModal = ref(false);
const showCropModal = ref(false);
const rawImageSource = ref(null);
const selectedPhoto = ref(null);
const fileInput = ref(null);
const photoPreview = ref(null);
const isCompressing = ref(false);

const form = useForm({
    photo: null,
    title: '',
    caption: '',
});

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    rawImageSource.value = URL.createObjectURL(file);
    showCropModal.value = true;
};

const onCropped = async (blob) => {
    showCropModal.value = false;

    // Create file from blob
    const file = new File([blob], 'cropped-memory.jpg', { type: 'image/jpeg' });
    photoPreview.value = URL.createObjectURL(file);

    if (file.size > 1024 * 1024) {
        isCompressing.value = true;
        const options = {
            maxSizeMB: 1,
            maxWidthOrHeight: 1920,
            useWebWorker: true
        };
        try {
            const compressedFile = await imageCompression(file, options);
            form.photo = compressedFile;
            photoPreview.value = URL.createObjectURL(compressedFile);
        } catch (error) {
            console.error('Compression failed:', error);
            form.photo = file;
        } finally {
            isCompressing.value = false;
        }
    } else {
        form.photo = file;
    }
};

const submitUpload = () => {
    if (isCompressing.value) return;
    form.clearErrors();

    if (!form.photo) {
        form.setError('photo', props.translations.errors.photo_required);
    }
    if (!form.title) {
        form.setError('title', props.translations.errors.title_required);
    }

    if (form.hasErrors) {
        return;
    }

    form.post(route('gallery.photo.upload', { album: props.album.id }), {
        forceFormData: true,
        onSuccess: () => {
            showUploadModal.value = false;
            form.reset();
            photoPreview.value = null;
        },
    });
};

const openLightbox = (photo) => {
    selectedPhoto.value = photo;
};

const resetModal = () => {
    showUploadModal.value = false;
    form.reset();
    photoPreview.value = null;
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="`${album.title} - ${translations.title}`" />

        <!-- Navigation -->
        <div class="mb-8">
            <Link :href="route('gallery.index')" class="inline-flex items-center gap-2 text-slate-400 hover:text-sky-600 font-bold text-sm transition-all group">
                <ChevronLeft :size="18" class="group-hover:-translate-x-1 transition-transform" />
                {{ translations.back_to_albums }}
            </Link>
        </div>

        <!-- Album Header -->
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-8 mb-16">
            <div class="max-w-3xl">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-4 py-1 bg-sky-50 text-sky-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-sky-100 shadow-sm">
                        {{ translations.collection }}
                    </span>
                    <div class="w-1 h-1 bg-slate-200 rounded-full"></div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        {{ translations.photos_count.replace(':count', album.photos.length) }}
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-slate-900 uppercase tracking-tight leading-tight mb-6">{{ album.title }}</h1>
                <p class="text-lg text-slate-500 font-medium leading-relaxed italic">
                    "{{ album.description || translations.no_description }}"
                </p>
            </div>

            <button @click="showUploadModal = true" class="flex items-center gap-2 px-8 py-5 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95 shrink-0">
                <Upload :size="18" /> {{ translations.upload_memory }}
            </button>
        </div>

        <!-- Photos Grid -->
        <div v-if="album.photos.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 animate-in fade-in zoom-in-95 duration-700">
            <div v-for="photo in album.photos" :key="photo.id"
                class="group relative aspect-square bg-white rounded-[2rem] border-4 border-white shadow-md hover:shadow-2xl transition-all duration-500 overflow-hidden cursor-pointer"
                @click="openLightbox(photo)"
            >
                <img :src="photo.file_path" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

                <!-- Overlay on Hover -->
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-end p-6">
                    <div class="text-white">
                        <p v-if="photo.title" class="font-black uppercase tracking-tight text-sm line-clamp-1 mb-1">{{ photo.title }}</p>
                        <p v-if="photo.caption" class="text-[10px] font-medium opacity-80 line-clamp-2 leading-relaxed">{{ photo.caption }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-[8px] font-black uppercase tracking-[0.2em] opacity-60">
                                <Calendar :size="10" /> {{ new Date(photo.uploaded_at).toLocaleDateString() }}  
                            </div>
                            <div class="p-2 bg-white/20 backdrop-blur-md rounded-lg">
                                <Maximize2 :size="14" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty Album State -->
        <div v-else class="py-32 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">        
            <div class="w-24 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center text-slate-200 mx-auto mb-6 shadow-inner">
                <ImageIcon :size="48" />
            </div>
            <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight">{{ translations.frame_waiting }}</h3>
            <p class="text-slate-400 max-w-sm mx-auto font-medium">{{ translations.empty_album_desc }}</p>
            <button @click="showUploadModal = true" class="mt-8 text-indigo-600 font-black uppercase tracking-widest text-xs hover:underline flex items-center gap-2 mx-auto">
                <Plus :size="14" /> {{ translations.upload_first }}
            </button>
        </div>

        <Modal :show="showUploadModal" :title="translations.add_memory_to_album" @close="resetModal">
            <div class="p-10 space-y-8">
                <div class="space-y-8">
                    <!-- Photo Picker & Preview -->
                    <div class="relative group">
                        <input
                            type="file"
                            ref="fileInput"
                            @change="handleFileSelect"
                            accept="image/*"
                            class="hidden"
                        >
                        <div v-if="!photoPreview"
                            @click="fileInput.click()"
                            class="py-16 border-4 border-dashed border-slate-100 rounded-[2.5rem] flex flex-col items-center justify-center cursor-pointer hover:bg-slate-50 hover:border-sky-200 transition-all group"
                        >
                            <div class="w-16 h-16 bg-sky-50 text-sky-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <Upload :size="32" />
                            </div>
                            <p class="text-sm font-black text-slate-900 uppercase tracking-widest mb-2">{{ translations.select_memory }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ translations.file_types }}</p>
                        </div>
                        <div v-else class="relative w-full aspect-video rounded-[2rem] overflow-hidden border-4 border-white shadow-xl">
                            <img :src="photoPreview" class="w-full h-full object-cover">
                            <button type="button" @click="photoPreview = null; form.photo = null" class="absolute top-4 right-4 p-2 bg-white/20 backdrop-blur-xl text-white rounded-xl hover:bg-rose-500 transition-all">       
                                <X :size="18" />
                            </button>
                        </div>
                        <div v-if="form.errors.photo" class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ form.errors.photo }}</div>
                    </div>

                    <!-- Title Input -->
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2 px-1">{{ translations.memory_title }}</label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <Type :size="18" />
                            </div>
                            <input
                                v-model="form.title"
                                type="text"
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-4 pl-12 text-sm font-bold focus:bg-white focus:border-indigo-500 outline-none transition-all"
                                :placeholder="translations.placeholder_memory_title"
                            >
                        </div>
                        <div v-if="form.errors.title" class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ form.errors.title }}</div>
                    </div>

                    <!-- Caption Input -->
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2 px-1">{{ translations.caption }}</label>
                        <div class="relative">
                            <div class="absolute left-4 top-4 text-slate-400">
                                <FileText :size="18" />
                            </div>
                            <textarea
                                v-model="form.caption"
                                rows="3"
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-4 pl-12 text-sm font-bold focus:bg-white focus:border-indigo-500 outline-none transition-all"
                                :placeholder="translations.placeholder_caption"
                            ></textarea>
                        </div>
                        <div v-if="form.errors.caption" class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ form.errors.caption }}</div>
                    </div>
                </div>

                <div class="flex gap-4 pt-6 border-t border-slate-50">
                    <button type="button" @click="resetModal" class="flex-1 px-8 py-5 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 transition-all flex items-center justify-center gap-2">
                        <RotateCw :size="16" /> {{ translations.cancel }}
                    </button>
                    <button type="button" @click="submitUpload" :disabled="form.processing || isCompressing" class="flex-1 px-8 py-5 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center justify-center gap-2">
                        <Loader2 v-if="form.processing || isCompressing" :size="16" class="animate-spin" />     
                        <Save v-else :size="16" /> {{ isCompressing ? translations.compressing : (form.processing ? translations.uploading : translations.preserve_memory) }}
                    </button>
                </div>
            </div>
        </Modal>

        <ImageCropper
            :show="showCropModal"
            :image-source="rawImageSource"
            @close="showCropModal = false"
            @cropped="onCropped"
        />

        <!-- Lightbox Modal -->
        <Modal :show="!!selectedPhoto" maxWidth="5xl" @close="selectedPhoto = null">
            <div v-if="selectedPhoto" class="relative group">
                <div class="max-h-[80vh] overflow-hidden bg-black flex items-center justify-center">
                    <img :src="selectedPhoto.file_path" class="max-w-full max-h-full object-contain">
                </div>

                <div class="p-10 bg-white">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-2">{{ selectedPhoto.title || translations.family_memory }}</h3>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                    <Calendar :size="12" /> {{ new Date(selectedPhoto.uploaded_at).toLocaleDateString() }}
                                </div>
                                <div class="w-1 h-1 bg-slate-200 rounded-full"></div>
                                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                    <User :size="12" /> {{ translations.contributed_by }}
                                </div>
                            </div>
                        </div>
                        <a :href="selectedPhoto.file_path" download class="p-4 bg-sky-50 text-sky-600 rounded-2xl hover:bg-sky-600 hover:text-white transition-all shadow-sm">
                            <Download :size="20" />
                        </a>
                    </div>
                    <p class="text-lg text-slate-600 font-medium leading-relaxed">
                        {{ selectedPhoto.caption || translations.no_caption_desc }}    
                    </p>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
.page-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}
</style>
