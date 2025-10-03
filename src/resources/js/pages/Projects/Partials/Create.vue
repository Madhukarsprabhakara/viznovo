<template>
    <Head title="Create a Project" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-screen items-start justify-center">
            <div class="w-full max-w-lg  rounded-xl border-1 shadow p-8 mt-12">
                <CreateProjectForm />
            </div>
        </div>
    </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import CreateProjectForm from './CreateProjectForm.vue';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
    {
        title: 'Create',
        href: '/projects/create',
    },


];
</script>