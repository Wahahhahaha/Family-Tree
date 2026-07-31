<script setup>
defineProps({
    systemname: String
})
</script>

<template>
    <footer class="w-full mt-auto py-8 border-t border-slate-100 text-center bg-white z-40">
        <div class="container mx-auto px-6">
            <p class="font-sora text-sm font-medium text-slate-500">
                &copy; {{ new Date().getFullYear() }} {{ systemname || 'Family Tree' }}. All rights reserved.
            </p>
        </div>
    </footer>
</template>
