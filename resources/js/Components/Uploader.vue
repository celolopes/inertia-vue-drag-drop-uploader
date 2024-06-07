<script setup>
import FileIcon from "./FileIcon.vue";
import UploadItem from "./UploadItem.vue";
import { onMounted, ref } from "vue";
import axios from "axios";
import { createUpload } from "@mux/upchunk";
import { router, usePage } from "@inertiajs/vue3";

onMounted(() => {
    Echo.private(`users.${usePage().props.auth.user.id}`)
        .listen("VideoEncodingStarted", (e) => {
            const upload = getUploadById(e.video_id);

            if (!upload) return;

            upload.encoding = true;
        })
        .listen("VideoEncodingProgress", (e) => {
            const upload = getUploadById(e.video_id);

            if (!upload) return;

            upload.encodingProgress = e.percentage;
        });
});

const uploads = ref([]);

const dropping = ref(false);

const handleFileChange = (files) => {
    Array.from(files).forEach((file) => {
        axios
            .post(route("videos.store"), {
                title: file.name,
            })
            .then((res) => {
                uploads.value.unshift({
                    id: res.data.id,
                    title: file.name,
                    file: startChunkedUpload(file, res.data.id),
                    uploading: true,
                    progress: 0,
                    encoding: false,
                    encodingProgress: 0,
                });
            });
    });
};

const getUploadById = (id) => {
    return uploads.value.find((upload) => upload.id === id);
};

const cancelUpload = (id) => {
    const upload = getUploadById(id);
    upload.file.abort();
    router.delete(route("videos.destroy", id), {
        preserveScrol: true,
        preserveState: true,
        onSuccess: () => {
            uploads.value = uploads.value.filter((upload) => upload.id !== id);
        },
    });
};

const pauseUpload = (id) => {
    const upload = getUploadById(id);
    if (upload && upload.file.pause) {
        upload.file.pause();
    }
};

const resumeUpload = (id) => {
    const upload = getUploadById(id);
    if (upload && upload.file.resume) {
        upload.file.resume();
    }
};

const startChunkedUpload = (file, id) => {
    const upload = createUpload({
        endpoint: route("videos.upload", id),
        method: "POST",
        file: file,
        chunkSize: 30 * 1024, //30 MB
        headers: {
            "X-CSRF-TOKEN": usePage().props.csrf_token,
        },
    });

    upload.on("progress", (progress) => {
        getUploadById(id).progress = progress.detail;
    });

    upload.on("success", () => {
        getUploadById(id).uploading = false;
    });

    return upload;
};
</script>

<template>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div
                class="overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 bg-white"
            >
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div
                        v-on:dragover.prevent="dropping = true"
                        v-on:dragleave.prevent="dropping = false"
                        v-on:drop.prevent="
                            handleFileChange($event.dataTransfer.files);
                            dropping = false;
                        "
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md dark:border-gray-600 border-gray-300"
                        :class="{
                            'border-indigo-600 dark:border-indigo-300':
                                dropping,
                        }"
                    >
                        <div class="space-y-1 text-center">
                            <FileIcon />
                            <div
                                class="flex text-sm text-gray-600 dark:text-gray-400"
                            >
                                <label
                                    for="file-upload"
                                    class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"
                                >
                                    <span class="px-1"
                                        >Carregue um arquivo</span
                                    >
                                    <input
                                        @change="
                                            handleFileChange(
                                                $event.target.files
                                            )
                                        "
                                        id="file-upload"
                                        name="file-upload"
                                        type="file"
                                        class="sr-only"
                                        multiple
                                    />
                                </label>
                                <p class="pl-1">ou arraste e solte</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                MP4, AVI acima de 100MB
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="my-3" v-for="upload in uploads" :key="uploads.id">
                <UploadItem
                    v-on:cancel="cancelUpload"
                    v-on:pause="pauseUpload"
                    v-on:resume="resumeUpload"
                    :upload="upload"
                />
            </div>
        </div>
    </div>
</template>
