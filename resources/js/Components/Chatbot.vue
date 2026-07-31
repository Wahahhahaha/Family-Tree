<script setup>
import { ref, nextTick, computed } from 'vue';
import { MessageSquare, X, Send, Bot, Loader2 } from 'lucide-vue-next';
import { useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const isOpen = ref(false);
const isLoading = ref(false);
const messagesContainer = ref(null);

// Get initial welcome message from translations
const messages = ref([
    { 
        role: 'assistant', 
        content: page.props.chatbot_translations?.welcome_message || 'Hello! I am your family digital assistant. How can I help you today?' 
    }
]);

const form = useForm({
    message: '',
});

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        scrollToBottom();
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const sendMessage = async () => {
    if (!form.message.trim() || isLoading.value) return;

    const userMsg = form.message;
    messages.value.push({ role: 'user', content: userMsg });
    form.message = '';
    isLoading.value = true;
    scrollToBottom();

    try {
        const response = await axios.post(route('chatbot.respond'), {
            message: userMsg,
            history: messages.value.slice(0, -1) // Send previous messages for context
        });

        if (response.data && response.data.reply) {
            messages.value.push({
                role: 'assistant',
                content: response.data.reply
            });
        }
    } catch (error) {
        console.error('Chatbot error:', error);
        messages.value.push({
            role: 'assistant',
            content: page.props.chatbot_translations?.error_message || "I'm sorry, I'm having trouble connecting to my neural network right now. Please try again later."
        });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};
</script>

<template>
    <div class="fixed inset-y-0 right-0 z-[100] pointer-events-none">
        <!-- Sidebar Chat Panel -->
        <div
            class="absolute top-0 right-0 h-full w-[400px] max-w-[90vw] bg-white shadow-[-20px_0_50px_rgba(0,0,0,0.1)] border-l border-slate-100 flex flex-col transition-transform duration-500 ease-in-out pointer-events-auto"
            :class="isOpen ? 'translate-x-0' : 'translate-x-full'"
        >
            <!-- Header -->
            <div class="p-8 bg-slate-900 text-white flex items-center justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/20 blur-3xl rounded-full"></div> 

                <div class="relative z-10 flex items-center gap-4">
                    <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl shadow-xl">
                        <Bot :size="24" />
                    </div>
                    <div>
                        <h3 class="text-base font-black uppercase tracking-[0.2em]">{{ __('header_title') }}</h3>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('header_subtitle') }}</p>
                        </div>
                    </div>
                </div>
                <button @click="toggleChat" class="relative z-10 p-2 hover:bg-white/10 rounded-xl transition-all active:scale-90">
                    <X :size="24" />
                </button>
            </div>

            <!-- Messages Area -->
            <div ref="messagesContainer" class="flex-1 overflow-y-auto p-8 space-y-6 bg-slate-50/30 scroll-smooth">
                <div v-for="(msg, index) in messages" :key="index"
                    class="flex flex-col" :class="msg.role === 'user' ? 'items-end' : 'items-start'"
                >
                    <div class="flex items-center gap-2 mb-2 text-[9px] font-black uppercase tracking-widest text-slate-400">
                        <span v-if="msg.role === 'assistant'">{{ __('bot_name') }}</span>
                        <span v-else>{{ __('user_name') }}</span>
                    </div>
                    <div class="max-w-[90%] p-5 rounded-[1.5rem] text-sm font-medium shadow-sm leading-relaxed" 
                        :class="msg.role === 'user'
                            ? 'bg-indigo-600 text-white rounded-tr-none shadow-indigo-100'
                            : 'bg-white text-slate-700 border border-slate-100 rounded-tl-none'"
                    >
                        {{ msg.content }}
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div v-if="isLoading" class="flex flex-col items-start animate-in fade-in">
                    <div class="flex items-center gap-2 mb-2 text-[9px] font-black uppercase tracking-widest text-slate-400">
                        <span>{{ __('bot_name') }}</span>
                    </div>
                    <div class="p-5 bg-white border border-slate-100 rounded-[1.5rem] rounded-tl-none shadow-sm flex items-center gap-2">
                        <Loader2 :size="16" class="animate-spin text-indigo-600" />
                        <span class="text-sm font-medium text-slate-500">{{ __('thinking') }}</span>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-8 bg-white border-t border-slate-100">
                <form @submit.prevent="sendMessage" class="relative group">
                    <div class="absolute inset-0 bg-indigo-100/50 blur-xl rounded-full opacity-0 group-focus-within:opacity-100 transition-opacity"></div>
                    <div class="relative flex items-center gap-3 bg-slate-50 border-2 border-slate-50 rounded-2xl p-2 pr-3 focus-within:border-indigo-100 focus-within:bg-white transition-all">
                        <input
                            v-model="form.message"
                            type="text"
                            :placeholder="__('input_placeholder')"
                            class="flex-1 bg-transparent border-none outline-none px-4 py-3 text-sm font-bold text-slate-700 placeholder:text-slate-300"
                        >
                        <button type="submit" class="p-3 bg-slate-900 text-white rounded-xl hover:bg-indigo-600 transition-all active:scale-95 shadow-lg">
                            <Send :size="18" />
                        </button>
                    </div>
                </form>
                <p class="mt-4 text-[9px] text-center text-slate-400 font-bold uppercase tracking-[0.2em]">     
                    {{ __('footer_text') }}
                </p>
            </div>
        </div>

        <!-- Floating Toggle Button -->
        <div class="absolute bottom-6 right-6 pointer-events-auto">
            <button
                v-if="!isOpen"
                @click="toggleChat"
                class="w-16 h-16 bg-slate-900 text-white rounded-[1.5rem] shadow-2xl shadow-slate-200 flex items-center justify-center hover:bg-indigo-600 transition-all active:scale-95 group relative overflow-hidden animate-in fade-in zoom-in duration-300"
            >
                <div class="absolute inset-0 bg-gradient-to-tr from-indigo-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <MessageSquare :size="28" class="relative z-10" />
            </button>
        </div>
    </div>
</template>
