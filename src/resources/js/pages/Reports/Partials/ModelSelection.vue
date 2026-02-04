<template>
  <fieldset>
    <legend class="text-sm/6 font-semibold text-gray-900 ">Select a model</legend>
    <div class=" grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
      <label v-for="mailingList in mailingLists" :key="mailingList.id" :aria-label="mailingList.title" :aria-description="`${mailingList.description} to ${mailingList.users}`" class="group relative flex rounded-lg border border-gray-300 bg-white p-4 has-[:disabled]:border-gray-400 has-[:disabled]:bg-gray-200 has-[:disabled]:opacity-25 has-[:checked]:outline has-[:focus-visible]:outline has-[:checked]:outline-2 has-[:focus-visible]:outline-[3px] has-[:checked]:-outline-offset-2 has-[:focus-visible]:-outline-offset-1 has-[:checked]:outline-indigo-600">
        <input type="radio" name="mailing-list" :value="mailingList.id" :checked="mailingList === mailingLists[0]" class="absolute inset-0 appearance-none focus:outline focus:outline-0" />
        <div class="flex-1">
          <span class="block text-sm font-medium text-gray-900">{{ mailingList.title }}</span>
          <span class="mt-1 block text-sm text-gray-500">{{ mailingList.description }}</span>
          
        </div>
        <CheckCircle class="invisible size-5 text-indigo-600 group-has-[:checked]:visible" aria-hidden="true" />
      </label>
    </div>
  </fieldset>
</template>

<script setup>
import { CheckCircle } from 'lucide-vue-next'
// ...existing code...
const mailingLists = [
  { id: 'newsletter', title: 'OpenAI - GPT 5', description: '400000 Tokens', users: '621 users' },
  {
    id: 'existing-customers',
    title: 'Google Gemini 3 Pro',
    description: '1M tokens',
    users: '1200 users',
  },
  
]
// ...existing code...
</script>