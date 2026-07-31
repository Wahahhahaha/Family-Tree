<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import Modal from './Modal.vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import { Scissors, X, Check, RotateCw, RefreshCw } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    imageSource: String,
    aspectRatio: {
        type: Number,
        default: 1
    }
});

const emit = defineEmits(['close', 'cropped']);

const imageElement = ref(null);
const cropper = ref(null);

const initCropper = () => {
    if (cropper.value) {
        cropper.value.destroy();
    }
    
    cropper.value = new Cropper(imageElement.value, {
        aspectRatio: props.aspectRatio,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 1,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
    });
};

const rotate = () => {
    cropper.value.rotate(90);
};

const reset = () => {
    cropper.value.reset();
};

const handleCrop = () => {
    const canvas = cropper.value.getCroppedCanvas({
        width: 1080,
        height: 1080,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob((blob) => {
        emit('cropped', blob);
    }, 'image/jpeg', 0.9);
};

watch(() => props.show, (newVal) => {
    if (newVal && props.imageSource) {
        setTimeout(initCropper, 100);
    }
});

onUnmounted(() => {
    if (cropper.value) {
        cropper.value.destroy();
    }
});
</script>

<template>
    <Modal :show="show" title="Crop Memory" @close="emit('close')" maxWidth="2xl">
        <div class="p-8 space-y-6">
            <div class="relative bg-slate-900 rounded-[2rem] overflow-hidden aspect-square flex items-center justify-center border-4 border-white shadow-2xl">
                <img ref="imageElement" :src="imageSource" class="max-w-full block">
            </div>

            <div class="flex items-center justify-between gap-4">
                <div class="flex gap-2">
                    <button @click="rotate" class="p-4 bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200 transition-all active:scale-90" title="Rotate 90°">
                        <RotateCw :size="20" />
                    </button>
                    <button @click="reset" class="p-4 bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200 transition-all active:scale-90" title="Reset">
                        <RefreshCw :size="20" />
                    </button>
                </div>

                <div class="flex gap-3 flex-1">
                    <button @click="emit('close')" class="flex-1 px-6 py-4 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">
                        Cancel
                    </button>
                    <button @click="handleCrop" class="flex-1 px-6 py-4 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center justify-center gap-2">
                        <Check :size="16" /> Apply Crop
                    </button>
                </div>
            </div>
        </div>
    </Modal>
</template>

<style>
/* Luxury styling for CropperJS UI */
.cropper-view-box,
.cropper-face {
    border-radius: 0;
}
.cropper-line {
    background-color: #6366f1;
}
.cropper-point {
    background-color: #6366f1;
    width: 8px;
    height: 8px;
}
.cropper-bg {
    background-image: none !important;
    background-color: #0f172a !important;
}
</style>
