<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'

import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { type BreadcrumbItem } from '@/types'

// ...existing code...

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'API Keys',
    href: '#',
  },
]

// 1) OpenAI form -> model_key = 'gpt-5'
const openAIForm = useForm({
  model_key: 'gpt-5',
  token: '',
})

// 2) Google form -> model_key = 'gemini-3-pro'
const geminiForm = useForm({
  model_key: 'gemini-3-pro',
  token: '',
})

function saveOpenAI() {
  openAIForm.post('/apikeys', {
    preserveScroll: true,
    onSuccess: () => openAIForm.reset('token'),
  })
}

function saveGemini() {
  geminiForm.post('/apikeys', {
    preserveScroll: true,
    onSuccess: () => geminiForm.reset('token'),
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="API Keys" />

    <SettingsLayout>
      <div class="flex flex-col space-y-6">
        <HeadingSmall title="API Keys" description="Manage your API keys for OpenAI and Google" />

        <!-- OpenAI -->
        <form class="space-y-6" @submit.prevent="saveOpenAI">
          <input type="hidden" name="model_key" :value="openAIForm.model_key" />

          <div class="grid gap-2">
            <Label for="openai_token">OpenAI Key (GPT-5)</Label>
            <Input
              id="openai_token"
              v-model="openAIForm.token"
              type="password"
              class="mt-1 block w-full"
              name="token"
              placeholder="OpenAI Key"
              autocomplete="off"
            />
            <InputError class="mt-2" :message="openAIForm.errors.token" />
            <InputError class="mt-2" :message="openAIForm.errors.model_key" />
          </div>

          <div class="flex items-center gap-4">
            <Button :disabled="openAIForm.processing">Save OpenAI Key</Button>
            <p v-if="openAIForm.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
          </div>
        </form>

        <!-- Google Gemini -->
        <form class="space-y-6" @submit.prevent="saveGemini">
          <input type="hidden" name="model_key" :value="geminiForm.model_key" />

          <div class="grid gap-2">
            <Label for="gemini_token">Google API Key (Gemini 3 Pro)</Label>
            <Input
              id="gemini_token"
              v-model="geminiForm.token"
              type="password"
              class="mt-1 block w-full"
              name="token"
              placeholder="Google Gemini API Key"
              autocomplete="off"
            />
            <InputError class="mt-2" :message="geminiForm.errors.token" />
            <InputError class="mt-2" :message="geminiForm.errors.model_key" />
          </div>

          <div class="flex items-center gap-4">
            <Button :disabled="geminiForm.processing">Save Google Key</Button>
            <p v-if="geminiForm.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
          </div>
        </form>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>