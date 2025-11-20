<template>
  <div class=" ">
    
    <div class="flex space-x-3">
      <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-gray-900">
          <Link :href="`/projects/${project.id}`" class="hover:underline">{{ project.name }}</Link>
        </p>
      </div>
      <div class="flex shrink-0 self-center">
        <Menu as="div" class="relative inline-block text-left">
          <MenuButton class="relative flex items-center rounded-full text-gray-400 outline-offset-[6px] hover:text-gray-600">
            <span class="absolute -inset-2"></span>
            <span class="sr-only">Open options</span>
            <MoreVertical class="size-5" aria-hidden="true" />
          </MenuButton>

          <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg outline outline-1 outline-black/5">
              <div class="py-1">
                <MenuItem v-slot="{ active }">
                  <Link
                    :href="`/projects/${project.id}/edit`"
                    :class="[active ? 'bg-gray-100 text-gray-900 outline-none' : 'text-gray-700', 'flex px-4 py-2 text-sm items-center']"
                  >
                    <Edit class="mr-3 w-5 h-5 text-gray-400" aria-hidden="true" />
                    <span>Edit</span>
                  </Link>
                </MenuItem>

                <MenuItem v-slot="{ active }">
                  <button
                    type="button"
                    @click="confirmDelete"
                    :class="[active ? 'bg-gray-100 text-gray-900 outline-none' : 'text-gray-700', 'flex px-4 py-2 text-sm items-center w-full text-left']"
                  >
                    <Trash2 class="mr-3 w-5 h-5 text-red-500" aria-hidden="true" />
                    <span>Delete</span>
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </transition>
        </Menu>
      </div>
    </div>
    <p class="text-sm mt-2 text-gray-500">
      {{ project.description }}
    </p>
    
  </div>
</template>

<script setup lang="ts">
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Edit, MoreVertical, Trash2 } from 'lucide-vue-next'
import { Link, useForm } from '@inertiajs/vue3'

interface Project {
  id: number
  name: string
  description: string
}
const form = useForm({});

const props = defineProps<{
  project: Project
}>()

const deleteProjectFinally = (projectId: number | string) => {
    form.delete('/projects/' + projectId, {
        errorBag: 'deleteProjectFile',
    });
};

function confirmDelete() {
  // simple confirm; replace with your modal if needed
 if (confirm(`Delete project "${props.project.name}"? This action cannot be undone.`)) {
    deleteProjectFinally(props.project.id);
  }
}
</script>