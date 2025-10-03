<template>
    <div class="bg-white">
        <form @submit.prevent="createProject">
            <div class="space-y-12">
                <div class="border-b border-gray-900/10 pb-12">


                    <div class="mt-2 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-4">
                            <label for="name" class="block text-sm/6 font-medium text-gray-900">Project Name</label>
                            <div class="mt-1">
                                <div
                                    class="flex items-center rounded-md bg-white pl-3 outline outline-1 -outline-offset-1 outline-gray-300 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-gray-600">
                                    <div class="shrink-0 select-none text-base text-gray-500 sm:text-sm/6">
                                    </div>
                                    <input type="text" name="name" id="name" v-model="form.name"
                                        class="block min-w-0 grow bg-white py-1.5 pl-1 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6"
                                        placeholder="acme inc" />
                                </div>
                            </div>
                        </div>

                        <div class="col-span-full">
                            <label for="description" class="block text-sm/6 font-medium text-gray-900">About</label>
                            <div class="mt-1">
                                <textarea name="description" v-model="form.description" id="description" rows="3"
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-gray-600 sm:text-sm/6" />
                            </div>
                            <p class="mt-1 text-sm/6 text-gray-600">Give a project description which can be used as
                                context for the AI model.</p>
                        </div>




                    </div>
                </div>



            </div>

            <div class="mt-6 flex items-center justify-end gap-x-6">
                <button type="button" class="text-sm/6 font-semibold text-gray-900">Cancel</button>
                <button type="submit"
                    class="rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">Save</button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: null,
    description: null,
})

const createProject = () => {
    form.post('/projects', {
        preserveScroll: true,
        onSuccess: () => form.reset('name', 'description'),
    });
};

</script>