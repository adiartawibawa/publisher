<script setup>
import { nextTick, onMounted, ref } from 'vue';
import emitter from 'tiny-emitter/instance';

import Modal from '@/Jetstream/DialogModal.vue';

const modalShown = ref(false);
let existingBlot = ref(null);
let content = ref('');

const props = defineProps({
    postId: {
        type: String,
    },
});

const emit = defineEmits([
    'adding',
]);

const applyHTML = () => {
    emit('adding', {
        content: content,
        existingBlot: existingBlot,
    });

    close();
}

const close = () => {
    modalShown.value = false;
    content = '';
    existingBlot = null;
}

onMounted(() => {
    emitter.on('openingHTMLEmbedder', data => {
        if (data) {
            content = data.content;
            existingBlot = data.existingBlot;
        }

        modalShown.value = true;

        nextTick(() => content.focus());
    });
});

</script>

<template>
    <Modal :show="modalShown" @close="close">
        <template #title>
            Embed HTML
        </template>
        <template #content>
            <textarea ref="content" cols="30" rows="10" class="input" placeholder="Paste your HTML here"
                v-model="content"></textarea>
        </template>
        <template #footer>
            <button class="border border-slate-800 text-slate-800 p-2 rounded no-underline "
                @click="applyHTML">Apply</button>
            <button class="border border-slate-800 text-slate-800 p-2 rounded no-underline ml-2"
                @click="close">Cancel</button>
        </template>
    </Modal>

</template>

<style lang="css">
.input {
    @apply mt-3 w-full bg-white p-0 appearance-none text-slate-800;
}

.input:focus {
    @apply outline-none;
}

textarea,
input {
    @apply bg-white text-slate-800;
}

/* input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus textarea:-webkit-autofill,
textarea:-webkit-autofill:hover textarea:-webkit-autofill:focus,
select:-webkit-autofill,
select:-webkit-autofill:hover,
select:-webkit-autofill:focus {
    border: none;
    -webkit-text-fill-color: config('colors.text-color');
    -webkit-box-shadow: 0 0 0px 1000px config('colors.contrast') inset;
    transition: background-color 5000s ease-in-out 0s;
    color: config('colors.text-color');
} */
</style>
