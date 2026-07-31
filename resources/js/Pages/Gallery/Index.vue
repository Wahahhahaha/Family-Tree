<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import ImageCropper from '@/Components/ui/ImageCropper.vue';
import { 
    Images, Plus, FolderPlus, FolderOpen, 
    Image as ImageIcon, MoreVertical, 
    Calendar, User, ChevronRight, Save, RotateCw, Loader2,
    Grid, LayoutDashboard, Maximize2, Download, Upload, X,
    Type, FileText
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import imageCompression from 'browser-image-compression';

const props = defineProps({
    galleryData: Object,
    allAlbums: Array,
    currentTab: String,
    translations: Object,
});

const itemsList = ref([...props.galleryData.data]);
const nextUrl = ref(props.galleryData.next_page_url);
const isLoading = ref(false);
const isCompressing = ref(false);
const showCreateModal = ref(false);
const showUploadModal = ref(false);
const showCropModal = ref(false);
const rawImageSource = ref(null);
const selectedPhoto = ref(null);
const fileInput = ref(null);
const photoPreview = ref(null);

const albumForm = useForm({
    title: '',
    description: '',
});

const uploadForm = useForm({
    album_id: '',
    photo: null,
    title: '',
    caption: '',
});

// Watch for tab changes to reset the local list
watch(() => props.currentTab, () => {
    itemsList.value = [...props.galleryData.data];
    nextUrl.value = props.galleryData.next_page_url;
});

const switchTab = (tab) => {
    router.get(route('gallery.index'), { tab }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            itemsList.value = [...page.props.galleryData.data];
            nextUrl.value = page.props.galleryData.next_page_url;
        }
    });
};

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
        const result = await response.json();

        itemsList.value = [...itemsList.value, ...result.data];
        nextUrl.value = result.next_page_url;
    } catch (error) {
        console.error('Failed to load more gallery items:', error);
    } finally {
        isLoading.value = false;
    }
};

const submitAlbum = () => {
    albumForm.post(route('gallery.album.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            albumForm.reset();
            // Refresh first page of current tab
            switchTab('albums');
        },
    });
};

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    rawImageSource.value = URL.createObjectURL(file);
    showCropModal.value = true;
};

const onCropped = async (blob) => {
    showCropModal.value = false;

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
            uploadForm.photo = compressedFile;
            photoPreview.value = URL.createObjectURL(compressedFile);
        } catch (error) {
            console.error('Compression failed:', error);
            uploadForm.photo = file;
        } finally {
            isCompressing.value = false;
        }
    } else {
        uploadForm.photo = file;
    }
};

const submitUpload = () => {
    if (isCompressing.value) return;
    uploadForm.clearErrors();

    if (!uploadForm.album_id) {
        uploadForm.setError('album_id', props.translations.errors.select_album);
    }
    if (!uploadForm.photo) {
        uploadForm.setError('photo', props.translations.errors.photo_required);
    }
    if (!uploadForm.title) {
        uploadForm.setError('title', props.translations.errors.title_required);
    }

    if (uploadForm.hasErrors) {
        return;
    }

    uploadForm.post(route('gallery.photo.upload', { album: uploadForm.album_id }), {
        forceFormData: true,
        onSuccess: () => {
            showUploadModal.value = false;
            uploadForm.reset();
            photoPreview.value = null;
            switchTab(props.currentTab);
        },
    });
};

const openLightbox = (photo) => {
    selectedPhoto.value = photo;
};

const resetUploadModal = () => {
    showUploadModal.value = false;
    uploadForm.reset();
    photoPreview.value = null;
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.title" />

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-black text-slate-900 flex items-center gap-3 mb-4">
                    <div class="p-2.5 bg-sky-100 text-sky-600 rounded-2xl">
                        <Images :size="32" />
                    </div>
                    {{ translations.title }}
                </h1>
                <p class="text-slate-500 font-medium leading-relaxed">
                    {{ translations.desc }}
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <button @click="showCreateModal = true" class="flex items-center gap-2 px-6 py-4 bg-white text-slate-600 border border-slate-100 rounded-2xl font-black text-xs uppercase tracking-widest shadow-sm hover:shadow-xl hover:border-sky-100 transition-all active:scale-95">
                    <FolderPlus :size="16" /> {{ translations.create_album }}
                </button>
                <button @click="showUploadModal = true" class="flex items-center gap-2 px-6 py-4 bg-sky-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-sky-100 hover:bg-sky-700 transition-all active:scale-95">
                    <Upload :size="16" /> {{ translations.upload_memory }}
                </button>
            </div>
        </div>

        <!-- Tab Switcher -->
        <div class="flex justify-center mb-12">
            <div class="bg-white p-1.5 rounded-[1.5rem] border border-slate-100 shadow-sm flex items-center gap-1">
                <button
                    @click="switchTab('albums')"
                    class="flex items-center gap-2 px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                    :class="currentTab === 'albums' ? 'bg-sky-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'"
                >
                    <LayoutDashboard :size="16" /> {{ translations.albums }}
                </button>
                <button
                    @click="switchTab('photos')"
                    class="flex items-center gap-2 px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                    :class="currentTab === 'photos' ? 'bg-sky-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'"
                >
                    <Grid :size="16" /> {{ translations.all_photos }}
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="space-y-12">
            <!-- Albums Tab View -->
            <div v-if="currentTab === 'albums'">
                <div v-if="itemsList.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 animate-in fade-in slide-in-from-bottom-8 duration-700">
                    <Link v-for="album in itemsList" :key="album.id" :href="route('gallery.show', album.id)"    
                        class="group flex flex-col bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-sky-100 hover:border-sky-100 transition-all duration-500 overflow-hidden"
                    >
                        <div class="aspect-[4/3] relative overflow-hidden bg-slate-50">
                            <img v-if="album.photos && album.photos.length"
                                :src="album.photos[0].file_path"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            >
                            <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-200">
                                <ImageIcon :size="48" stroke-width="1.5" />
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] mt-4">{{ translations.empty_album }}</span>
                            </div>
                            <div class="absolute top-4 right-4 px-3 py-1.5 bg-black/40 backdrop-blur-md rounded-full text-white text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5">
                                <ImageIcon :size="12" /> {{ translations.photos_count.replace(':count', album.photos_count || 0) }}
                            </div>
                        </div>

                        <div class="p-8 flex-1 flex flex-col">
                            <h3 class="text-lg font-black text-slate-900 group-hover:text-sky-600 transition-colors uppercase tracking-tight mb-2 line-clamp-1">
                                {{ album.title }}
                            </h3>
                            <p class="text-slate-400 text-xs font-medium line-clamp-2 leading-relaxed mb-6">    
                                {{ album.description || translations.no_description }}       
                            </p>
                            <div class="mt-auto flex items-center justify-between pt-6 border-t border-slate-50">
                                <div class="flex items-center gap-2 text-[9px] font-black uppercase tracking-widest text-slate-300">
                                    <Calendar :size="12" /> {{ new Date(album.created_at).toLocaleDateString('en-GB', { month: 'short', year: 'numeric' }) }}
                                </div>
                                <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-sky-600 group-hover:text-white transition-all duration-500">
                                    <ChevronRight :size="16" />
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
                <div v-else class="py-24 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">
                    <div class="w-24 h-24 bg-sky-50 rounded-[2.5rem] flex items-center justify-center text-sky-200 mx-auto mb-6 shadow-inner">
                        <FolderOpen :size="48" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight">{{ translations.canvas_empty }}</h3>
                    <button @click="showCreateModal = true" class="mt-8 text-sky-600 font-black uppercase tracking-widest text-xs hover:underline flex items-center gap-2 mx-auto">
                        <Plus :size="14" /> {{ translations.begin_gallery }}
                    </button>
                </div>
            </div>

            <!-- Photos Tab View -->
            <div v-else-if="currentTab === 'photos'">
                <div v-if="itemsList.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 animate-in fade-in slide-in-from-bottom-8 duration-700">
                    <div v-for="photo in itemsList" :key="photo.id"
                        @click="openLightbox(photo)"
                        class="group relative aspect-square bg-white rounded-2xl border-2 border-white shadow-sm hover:shadow-xl transition-all duration-500 overflow-hidden cursor-pointer"
                    >
                        <img :src="photo.file_path" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <Maximize2 :size="20" class="text-white" />
                        </div>
                    </div>
                </div>
                <div v-else class="py-24 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">
                    <div class="w-24 h-24 bg-sky-50 rounded-[2.5rem] flex items-center justify-center text-sky-200 mx-auto mb-6 shadow-inner">
                        <ImageIcon :size="48" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight">{{ translations.no_photos }}</h3>
                    <p class="text-slate-400 font-medium">{{ translations.no_photos_desc }}</p>      
                    <button @click="showUploadModal = true" class="mt-8 text-sky-600 font-black uppercase tracking-widest text-xs hover:underline flex items-center gap-2 mx-auto">
                        <Plus :size="14" /> {{ translations.start_contributing }}
                    </button>
                </div>
            </div>

            <!-- View More Button -->
            <div v-if="nextUrl" class="flex justify-center pb-12">
                <button
                    @click="loadMore"
                    :disabled="isLoading"
                    class="group flex items-center gap-3 px-10 py-4 bg-white border border-slate-200 rounded-3xl font-black text-xs uppercase tracking-[0.2em] text-slate-500 hover:border-sky-300 hover:text-sky-600 hover:shadow-xl hover:shadow-indigo-100 transition-all disabled:opacity-50"
                >
                    <Loader2 v-if="isLoading" :size="18" class="animate-spin" />
                    <Plus v-else :size="18" class="group-hover:rotate-90 transition-transform duration-500" />  
                    {{ isLoading ? translations.loading : translations.view_more.replace(':type', currentTab === 'albums' ? translations.albums : translations.all_photos) }}
                </button>
            </div>
        </div>

        <!-- Create Album Modal -->
        <Modal :show="showCreateModal" :title="translations.create_new_album" @close="showCreateModal = false">
            <form @submit.prevent="submitAlbum" class="p-10 space-y-8">
                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.album_title }}</label>
                        <input v-model="albumForm.title" type="text" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-sky-500 outline-none transition-all" :placeholder="translations.placeholder_album_title">
                        <div v-if="albumForm.errors.title" class="text-rose-500 text-[10px] mt-1.5 font-bold uppercase tracking-wider">{{ albumForm.errors.title }}</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations.description }}</label>
                        <textarea v-model="albumForm.description" rows="4" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-sky-500 outline-none" :placeholder="translations.placeholder_album_desc"></textarea>
                    </div>
                </div>
                <div class="flex gap-4 pt-6 border-t border-slate-50">
                    <button type="button" @click="showCreateModal = false" class="flex-1 px-8 py-5 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 flex items-center justify-center gap-2"><RotateCw :size="16" /> {{ translations.discard }}</button>
                    <button type="submit" :disabled="albumForm.processing" class="flex-1 px-8 py-5 bg-sky-600 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-sky-100 hover:bg-sky-700 flex items-center justify-center gap-2">
                        <Loader2 v-if="albumForm.processing" :size="16" class="animate-spin" />
                        <Save v-else :size="16" /> {{ albumForm.processing ? translations.creating : translations.create_button }}  
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Global Upload Modal -->
        <Modal :show="showUploadModal" :title="translations.add_memory" @close="resetUploadModal">
            <div class="p-10 space-y-8">
                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-sky-600 uppercase tracking-widest block mb-2">{{ translations.target_album }}</label>
                        <select v-model="uploadForm.album_id" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-sky-500 outline-none transition-all">
                            <option value="">{{ translations.select_album_placeholder }}</option>
                            <option v-for="album in allAlbums" :key="album.id" :value="album.id">{{ album.title }}</option>
                        </select>
                        <p class="text-[9px] text-slate-400 mt-2 italic">{{ translations.heritage_note }}</p>
                        <div v-if="uploadForm.errors.album_id" class="text-rose-500 text-[10px] mt-1.5 font-bold uppercase tracking-wider">{{ uploadForm.errors.album_id }}</div>
                    </div>

                    <div class="relative group">
                        <input type="file" ref="fileInput" @change="handleFileSelect" accept="image/*" class="hidden">
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
                            <button type="button" @click="photoPreview = null; uploadForm.photo = null" class="absolute top-4 right-4 p-2 bg-white/20 backdrop-blur-xl text-white rounded-xl hover:bg-rose-500 transition-all"> 
                                <X :size="18" />
                            </button>
                        </div>
                        <div v-if="uploadForm.errors.photo" class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ uploadForm.errors.photo }}</div>
                    </div>

                    <!-- Title Input -->
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2 px-1">{{ translations.memory_title }}</label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <Type :size="18" />
                            </div>
                            <input
                                v-model="uploadForm.title"
                                type="text"
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-4 pl-12 text-sm font-bold focus:bg-white focus:border-indigo-500 outline-none transition-all"
                                :placeholder="translations.placeholder_memory_title"
                            >
                        </div>
                        <div v-if="uploadForm.errors.title" class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ uploadForm.errors.title }}</div>
                    </div>

                    <!-- Caption Input -->
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2 px-1">{{ translations.caption }}</label>
                        <div class="relative">
                            <div class="absolute left-4 top-4 text-slate-400">
                                <FileText :size="18" />
                            </div>
                            <textarea
                                v-model="uploadForm.caption"
                                rows="3"
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-4 pl-12 text-sm font-bold focus:bg-white focus:border-indigo-500 outline-none transition-all"
                                :placeholder="translations.placeholder_caption"
                            ></textarea>
                        </div>
                        <div v-if="uploadForm.errors.caption" class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ uploadForm.errors.caption }}</div>
                    </div>
                </div>

                <div class="flex gap-4 pt-6 border-t border-slate-50">
                    <button type="button" @click="resetUploadModal" class="flex-1 px-8 py-5 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 flex items-center justify-center gap-2"><RotateCw :size="16" /> {{ translations.cancel }}</button>
                    <button type="button" @click="submitUpload" :disabled="uploadForm.processing || isCompressing" class="flex-1 px-8 py-5 bg-sky-600 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-indigo-100 hover:bg-sky-700 flex items-center justify-center gap-2">
                        <Loader2 v-if="uploadForm.processing || isCompressing" :size="16" class="animate-spin" />
                        <Save v-else :size="16" /> {{ isCompressing ? translations.compressing : (uploadForm.processing ? translations.uploading : translations.preserve_memory) }}
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
            <div v-if="selectedPhoto" class="relative">
                <div class="max-h-[80vh] overflow-hidden bg-black flex items-center justify-center">
                    <img :src="selectedPhoto.file_path" class="max-w-full max-h-full object-contain">
                </div>
                <div class="p-10 bg-white">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">{{ selectedPhoto.title || translations.family_memory }}</h3>
                        <a :href="selectedPhoto.file_path" download class="p-4 bg-sky-50 text-sky-600 rounded-2xl hover:bg-sky-600 hover:text-white transition-all shadow-sm">
                            <Download :size="20" />
                        </a>
                    </div>
                    <p class="text-slate-600 font-medium">{{ selectedPhoto.caption || translations.no_caption }}</p>
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
