<script setup>
import Modal from './Modal.vue';
import { useAlert } from '@/Composables/useAlert';
import { 
    AlertCircle, CheckCircle2, 
    Info, HelpCircle, XCircle 
} from 'lucide-vue-next';

const { state, close } = useAlert();

const getIcon = () => {
    switch (state.variant) {
        case 'success': return CheckCircle2;
        case 'error': return XCircle;
        case 'warning': return AlertCircle;
        default: return Info;
    }
};

const getIconColor = () => {
    switch (state.variant) {
        case 'success': return 'text-emerald-500 bg-emerald-50';
        case 'error': return 'text-rose-500 bg-rose-50';
        case 'warning': return 'text-amber-500 bg-amber-50';
        default: return 'text-sky-500 bg-sky-50';
    }
};

const getButtonClass = () => {
    switch (state.variant) {
        case 'success': return 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-100';
        case 'error': return 'bg-rose-600 hover:bg-rose-700 shadow-rose-100';
        case 'warning': return 'bg-amber-600 hover:bg-amber-700 shadow-amber-100';
        default: return 'bg-sky-600 hover:bg-sky-700 shadow-sky-100';
    }
};
</script>

<template>
    <Modal :show="state.show" maxWidth="md" @close="close(false)">
        <div class="p-8">
            <div class="text-center">
                <div :class="['w-20 h-20 rounded-[1.5rem] flex items-center justify-center mx-auto mb-6 shadow-sm', getIconColor()]">
                    <component :is="getIcon()" :size="40" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-2">{{ state.title }}</h3>
                <p class="text-slate-500 font-medium leading-relaxed">{{ state.message }}</p>
            </div>

            <div class="mt-8 flex gap-3">
                <template v-if="state.type === 'confirm'">
                    <button 
                        @click="close(false)"
                        class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-slate-200 transition-all"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="close(true)"
                        class="flex-1 px-6 py-4 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-lg transition-all"
                        :class="getButtonClass()"
                    >
                        Confirm
                    </button>
                </template>
                <template v-else>
                    <button 
                        @click="close(true)"
                        class="w-full px-6 py-4 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-lg transition-all"
                        :class="getButtonClass()"
                    >
                        Understood
                    </button>
                </template>
            </div>
        </div>
    </Modal>
</template>
