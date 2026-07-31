<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import Footer from '@/Components/ui/Footer.vue';

const props = usePage().props;
</script>

<template>
    <Head>
        <title>Family Trees</title>
    </Head>
    <div class="min-h-screen flex flex-col bg-white">
        <main class="flex-grow">
            <slot />
        </main>
        <Footer :systemname="props.systemname" />
    </div>
</template>
