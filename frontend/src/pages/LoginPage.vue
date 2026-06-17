<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'primevue/usetoast'

const auth = useAuthStore()
const router = useRouter()
const toast = useToast()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errors = ref<Record<string, string[]>>({})

async function handleLogin() {
  loading.value = true
  errors.value = {}

  try {
    await auth.login(email.value, password.value)
    if (auth.user?.role === 'mentor') router.push('/mentor/dashboard')
    else if (auth.user?.role === 'admin') router.push('/admin/users')
    else router.push('/dashboard')
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
      toast.add({ severity: 'error', summary: 'Greška', detail: 'Pogrešan email ili lozinka.', life: 3000 })
    } else {
      toast.add({ severity: 'error', summary: 'Greška', detail: 'Nešto je pošlo po krivu. Pokušaj ponovo.', life: 3000 })
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex">

    <div class="hidden lg:flex lg:w-1/2 bg-indigo-600 flex-col items-center justify-center p-12">
      <div class="text-center text-white max-w-md">
        <h1 class="text-4xl font-bold mb-4">ProjectFlow</h1>
        <p class="text-lg text-indigo-200 leading-relaxed">
          Platforma za prijavu, praćenje i ocjenjivanje završnih radova
        </p>
      </div>
    </div>


    <div class="flex-1 flex items-center justify-center bg-gray-50 p-8">
      <div class="w-full max-w-md">

        <div class="lg:hidden text-center mb-8">
          <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <i class="pi pi-graduation-cap text-2xl text-white" />
          </div>
          <h1 class="text-2xl font-bold text-gray-900">ProjectFlow</h1>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-1">Dobrodošli nazad</h2>
          <p class="text-gray-500 mb-7 text-sm">Unesite vaše podatke za pristup platformi</p>

          <form @submit.prevent="handleLogin" class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Email adresa</label>
              <InputText
                v-model="email"
                type="email"
                placeholder="vase@email.com"
                :invalid="!!errors.email"
                class="w-full"
                autofocus
              />
              <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Lozinka</label>
              <Password
                v-model="password"
                placeholder="Vaša lozinka"
                :feedback="false"
                toggleMask
                class="w-full"
                inputClass="w-full"
              />
              <p v-if="errors.password" class="mt-1 text-xs text-red-500">{{ errors.password[0] }}</p>
            </div>

            <Button
              type="submit"
              label="Prijavi se"
              icon="pi pi-arrow-right"
              iconPos="right"
              :loading="loading"
              class="w-full mt-2"
            />
          </form>

          <p class="text-center text-gray-500 mt-6 text-sm">
            Nemate račun?
            <RouterLink to="/register" class="text-indigo-600 font-medium hover:text-indigo-500">
              Registrirajte se
            </RouterLink>
          </p>
        </div>


        <div class="mt-4 bg-indigo-50 border border-indigo-100 rounded-xl p-4">
          <p class="text-xs font-medium text-indigo-700 mb-2">Test korisnici (password: password)</p>
          <div class="space-y-1">
            <p class="text-xs text-indigo-600"><span class="font-medium">Admin:</span> admin@projectflow.com</p>
            <p class="text-xs text-indigo-600"><span class="font-medium">Mentor:</span> ana.kovac@projectflow.com</p>
            <p class="text-xs text-indigo-600"><span class="font-medium">Student:</span> marko@student.com</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

