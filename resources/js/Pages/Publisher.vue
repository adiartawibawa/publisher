<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Editor from '@/Components/Editor/Editor.vue';

</script>

<template>
    <AppLayout title="Publisher">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Publisher
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="px-10 pb-10 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <Editor :post-id="1" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>