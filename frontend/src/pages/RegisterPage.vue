<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'primevue/usetoast'

const auth = useAuthStore()
const router = useRouter()
const toast = useToast()

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const role = ref('student')
const loading = ref(false)
const errors = ref<Record<string, string[]>>({})

const roleOptions = [
  { label: 'Student', value: 'student' },
  { label: 'Mentor / Profesor', value: 'mentor' },
]

async function handleRegister() {
  loading.value = true
  errors.value = {}

  try {
    await auth.register(name.value, email.value, password.value, password_confirmation.value, role.value)
    if (auth.user?.role === 'mentor') router.push('/mentor/dashboard')
    else router.push('/dashboard')
    toast.add({ severity: 'success', summary: 'Dobrodošli!', detail: `Račun je kreiran za ${auth.user?.name}.`, life: 3000 })
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      toast.add({ severity: 'error', summary: 'Greška', detail: 'Registracija nije uspjela.', life: 3000 })
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
        <div class="w-20 h-20 bg-indigo-500 rounded-3xl flex items-center justify-center mx-auto mb-8">
          <i class="pi pi-graduation-cap text-4xl text-white" />
        </div>
        <h1 class="text-4xl font-bold mb-4">ProjectFlow</h1>
        <p class="text-lg text-indigo-200 leading-relaxed">
          Pridruži se platformi i počni pratiti napredak svog završnog rada
        </p>

        <div class="mt-10 bg-indigo-700 rounded-2xl p-6 text-left">
          <p class="text-sm font-semibold text-indigo-200 uppercase tracking-wider mb-4">Uloge na platformi</p>
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                <i class="pi pi-book text-white text-sm" />
              </div>
              <div>
                <p class="text-white text-sm font-medium">Student</p>
                <p class="text-indigo-200 text-xs">Kreira i prati napredak projekta</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                <i class="pi pi-user text-white text-sm" />
              </div>
              <div>
                <p class="text-white text-sm font-medium">Mentor / Profesor</p>
                <p class="text-indigo-200 text-xs">Pregledava i ocjenjuje radove</p>
              </div>
            </div>
          </div>
        </div>
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
          <h2 class="text-2xl font-bold text-gray-900 mb-1">Kreiraj račun</h2>
          <p class="text-gray-500 mb-7 text-sm">Ispuni podatke i počni koristiti platformu</p>

          <form @submit.prevent="handleRegister" class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Ime i prezime</label>
              <InputText v-model="name" placeholder="Marko Horvat" :invalid="!!errors.name" class="w-full" autofocus />
              <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Email adresa</label>
              <InputText v-model="email" type="email" placeholder="vase@email.com" :invalid="!!errors.email" class="w-full" />
              <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Lozinka</label>
              <Password v-model="password" placeholder="Min. 8 znakova" toggleMask :feedback="false" class="w-full" inputClass="w-full" />
              <p v-if="errors.password" class="mt-1 text-xs text-red-500">{{ errors.password[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Potvrdi lozinku</label>
              <Password v-model="password_confirmation" placeholder="Ponovi lozinku" toggleMask :feedback="false" class="w-full" inputClass="w-full" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Uloga</label>
              <SelectButton
                v-model="role"
                :options="roleOptions"
                optionLabel="label"
                optionValue="value"
                class="w-full"
              />
            </div>

            <Button
              type="submit"
              label="Registriraj se"
              icon="pi pi-arrow-right"
              iconPos="right"
              :loading="loading"
              class="w-full mt-2"
            />
          </form>

          <p class="text-center text-gray-500 mt-6 text-sm">
            Već imate račun?
            <RouterLink to="/login" class="text-indigo-600 font-medium hover:text-indigo-500">
              Prijavite se
            </RouterLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

