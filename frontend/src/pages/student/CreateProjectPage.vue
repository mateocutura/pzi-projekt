<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import api from '@/lib/axios'
import { useToast } from 'primevue/usetoast'

const router = useRouter()
const toast = useToast()

const title = ref('')
const description = ref('')
const vision = ref('')
const github_url = ref('')
const loading = ref(false)
const errors = ref<Record<string, string[]>>({})

async function handleSubmit() {
  loading.value = true
  errors.value = {}

  try {
    const response = await api.post('/projects', {
      title: title.value,
      description: description.value,
      vision: vision.value,
      github_url: github_url.value || null,
    })
    toast.add({ severity: 'success', summary: 'Uspjeh!', detail: 'Projekt je prijavljen. Čeka odobrenje mentora.', life: 4000 })
    router.push(`/projects/${response.data.id}`)
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      toast.add({ severity: 'error', summary: 'Greška', detail: 'Prijava projekta nije uspjela.', life: 3000 })
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <MainLayout>
    <div class="max-w-2xl mx-auto">
      <div class="flex items-center gap-3 mb-6">
        <button
          @click="router.back()"
          class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-gray-800 hover:border-gray-300 transition-colors"
        >
          <i class="pi pi-arrow-left text-sm" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Prijava novog projekta</h1>
          <p class="text-sm text-gray-500">Popuni sve obavezne podatke o svom završnom radu</p>
        </div>
      </div>

      <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
        <form @submit.prevent="handleSubmit" class="space-y-5">

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Naziv projekta <span class="text-red-500">*</span></label>
            <InputText
              v-model="title"
              placeholder="npr. Platforma za online učenje"
              :invalid="!!errors.title"
              class="w-full"
              autofocus
            />
            <p v-if="errors.title" class="mt-1 text-xs text-red-500">{{ errors.title[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Opis projekta <span class="text-red-500">*</span></label>
            <Textarea
              v-model="description"
              placeholder="Kratko opišite o čemu se projekt radi..."
              rows="4"
              :invalid="!!errors.description"
              class="w-full"
            />
            <p v-if="errors.description" class="mt-1 text-xs text-red-500">{{ errors.description[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Vizija projekta <span class="text-red-500">*</span></label>
            <Textarea
              v-model="vision"
              placeholder="Što želite postići ovim projektom? Koji problem rješavate?"
              rows="4"
              :invalid="!!errors.vision"
              class="w-full"
            />
            <p v-if="errors.vision" class="mt-1 text-xs text-red-500">{{ errors.vision[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">GitHub URL <span class="text-gray-400 font-normal">(opcionalno)</span></label>
            <InputText
              v-model="github_url"
              placeholder="https://github.com/korisnik/repozitorij"
              :invalid="!!errors.github_url"
              class="w-full"
            />
            <p v-if="errors.github_url" class="mt-1 text-xs text-red-500">{{ errors.github_url[0] }}</p>
          </div>

          <div class="flex gap-3 justify-end pt-2 border-t border-gray-100">
            <Button label="Odustani" severity="secondary" outlined @click="router.back()" />
            <Button type="submit" label="Prijavi projekt" icon="pi pi-send" :loading="loading" />
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>
