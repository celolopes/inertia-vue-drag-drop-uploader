<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Uploader from '@/Components/Uploader.vue';
import { onMounted } from 'vue';

/* onMounted(() => {
    Echo.channel("videos").listen("Test", (e) => {
        console.log(e);
    });
}) */
</script>

<template>
    <Head title="Uploader" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Uploader</h2>
        </template>

        <Uploader />
    </AuthenticatedLayout>
</template>
