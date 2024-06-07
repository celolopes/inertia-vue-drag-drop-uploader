<script setup>
import InputLabel from "./InputLabel.vue";
import PrimaryButton from "./PrimaryButton.vue";
import TextInput from "./TextInput.vue";
import TextArea from "./TextArea.vue";
import { useForm } from "@inertiajs/vue3";
import InputError from "./InputError.vue";


const emit = defineEmits(['pause', 'cancel', 'resume']);

const props = defineProps({
    upload: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    title: props.upload.title,
    description: "",
});

const update = async () => {
    await form.put(route("videos.update", props.upload.id), {
        preserveScrol: true,
        preserveState: true,
    });
};
</script>

<template>
    <div
        class="overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 bg-white"
    >
        <div class="p-6 dark:border-gray-700 border-gray-200">
            <div class="flex space-x-6">
                <div class="max-w-[240px] w-full space-y-3">
                    <div v-if="upload.uploading" class="space-y-1">
                        <div
                            class="bg-gray-800 dark:bg-gray-700 shadow-inner h-3 rounded overflow-hidden"
                        >
                            <div
                                class="h-full bg-indigo-600 rounded-full"
                                v-bind:style="{ width: `${upload.progress}%` }"
                            ></div>
                        </div>
                        <div class="text-sm text-gray-100 dark:text-gray-300">
                            Carregando
                        </div>
                    </div>
                    <div class="space-y-1" v-if="upload.encoding">
                        <div
                            class="bg-gray-800 dark:bg-gray-700 shadow-inner h-3 rounded overflow-hidden"
                        >
                            <div
                                class="h-full bg-green-500 rounded-full"
                                v-bind:style="{ width: `${upload.encodingProgress}%` }"
                            ></div>
                        </div>
                        <div class="text-sm text-gray-100 dark:text-gray-300">
                            Encodando
                        </div>
                    </div>
                    <div class="flex items-center space-x-3" v-if="upload.uploading">
                        <button
                            v-if="!upload.file.paused"
                            @click="emit('pause', upload.id)"
                            class="text-gray-300 dark:text-gray-400 text-sm font-medium"
                        >
                            Pausar
                        </button>
                        <button
                            v-if="upload.file.paused"
                            @click="emit('resume', upload.id)"
                            class="text-gray-300 dark:text-gray-400 text-sm font-medium"
                        >
                            Resumir
                        </button>
                        <button
                            v-if="upload.uploading"
                            @click="emit('cancel', upload.id)"
                            class="text-gray-300 dark:text-gray-400 text-sm font-medium"
                        >
                            Cancelar carregamento
                        </button>
                    </div>
                </div>
                <form @submit.prevent="update" class="flex-grow space-y-6">
                    <div>
                        <InputLabel for="title" value="Título" />
                        <TextInput
                            v-model="form.title"
                            id="title"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div>
                        <InputLabel for="description" value="Descrição" />
                        <TextArea
                            v-model="form.description"
                            id="description"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.description" />
                    </div>
                    <div class="flex items-center space-x-3">
                        <PrimaryButton>Salvar</PrimaryButton>
                        <div v-if="form.recentlySuccessful" class="text-sm text-gray-300 dark:text-gray-400">
                            Vídeo carregado
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
