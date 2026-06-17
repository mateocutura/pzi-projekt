<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import api from '@/lib/axios'
import { useToast } from 'primevue/usetoast'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const project = ref<any>(null)
const phases = ref<any[]>([])
const loading = ref(true)

const activePhaseId = ref<number | null>(null)
const submissionText = ref('')
const submitting = ref(false)

onMounted(async () => {
  await loadProject()
})

async function loadProject() {
  loading.value = true
  try {
    const response = await api.get(`/projects/${route.params.id}`)
    project.value = response.data.project
    phases.value = response.data.phases
  } catch {
    toast.add({ severity: 'error', summary: 'Greška', detail: 'Projekt nije pronađen.', life: 3000 })
    router.push('/dashboard')
  } finally {
    loading.value = false
  }
}

function openSubmitForm(phaseId: number) {
  activePhaseId.value = phaseId
  submissionText.value = ''
}

async function submitPhase() {
  if (!activePhaseId.value) return
  submitting.value = true

  try {
    await api.post(`/projects/${project.value.id}/submissions`, {
      phase_id: activePhaseId.value,
      submission_text: submissionText.value,
    })
    toast.add({ severity: 'success', summary: 'Predano!', detail: 'Faza je predana mentoru na pregled.', life: 3000 })
    activePhaseId.value = null
    await loadProject() 
  } catch (error: any) {
    const msg = error.response?.data?.message || 'Predaja nije uspjela.'
    toast.add({ severity: 'error', summary: 'Greška', detail: msg, life: 3000 })
  } finally {
    submitting.value = false
  }
}

function phaseStatusIcon(status: string | undefined) {
  if (status === 'approved') return 'pi pi-check-circle text-green-500'
  if (status === 'submitted') return 'pi pi-clock text-yellow-500'
  if (status === 'rejected') return 'pi pi-times-circle text-red-500'
  return 'pi pi-circle text-gray-300'
}

function phaseStatusLabel(status: string | undefined) {
  if (status === 'approved') return 'Odobreno'
  if (status === 'submitted') return 'Na pregledu'
  if (status === 'rejected') return 'Odbijeno'
  return 'Nije predano'
}

function projectStatusSeverity(status: string) {
  if (status === 'accepted') return 'success'
  if (status === 'rejected') return 'danger'
  return 'warn'
}

function projectStatusLabel(status: string) {
  if (status === 'accepted') return 'Prihvaćeno'
  if (status === 'rejected') return 'Odbijeno'
  return 'Na čekanju'
}

const canSubmit = computed(() => project.value?.status === 'accepted')
</script>

<template>
  <MainLayout>
    <div v-if="loading" class="flex justify-center py-20">
      <ProgressSpinner />
    </div>

    <div v-else-if="project">

      <div class="flex items-center gap-3 mb-6">
        <Button icon="pi pi-arrow-left" severity="secondary" text @click="router.push('/dashboard')" />
        <div class="flex-1">
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-800">{{ project.title }}</h1>
            <Tag
              :severity="projectStatusSeverity(project.status)"
              :value="projectStatusLabel(project.status)"
            />
          </div>
          <p v-if="project.mentor" class="text-sm text-gray-500 mt-1">
            Mentor: {{ project.mentor.name }}
          </p>
        </div>
      </div>


      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border rounded-xl p-4 shadow-sm col-span-2">
          <h3 class="font-semibold text-gray-700 mb-2">Opis</h3>
          <p class="text-gray-600">{{ project.description }}</p>
          <h3 class="font-semibold text-gray-700 mt-4 mb-2">Vizija</h3>
          <p class="text-gray-600">{{ project.vision }}</p>
          <div v-if="project.github_url" class="mt-4">
            <a :href="project.github_url" target="_blank" class="text-indigo-600 flex items-center gap-2 hover:underline">
              <i class="pi pi-github" />
              GitHub repozitorij
            </a>
          </div>
          <div v-if="project.rejection_reason" class="mt-4 p-3 bg-red-50 rounded-lg">
            <p class="text-sm text-red-600 font-medium">Razlog odbijanja:</p>
            <p class="text-sm text-red-600 mt-1">{{ project.rejection_reason }}</p>
          </div>
        </div>


        <div class="bg-white border rounded-xl p-4 shadow-sm">
          <h3 class="font-semibold text-gray-700 mb-3">Ukupan napredak</h3>
          <div class="text-4xl font-bold text-indigo-600 text-center my-4">
            {{ project.progress_percentage }}%
          </div>
          <ProgressBar :value="project.progress_percentage" />
          <p class="text-sm text-gray-500 text-center mt-2">
            {{ Math.round(project.progress_percentage / 10) }} / 10 faza završeno
          </p>
        </div>
      </div>


      <div class="bg-white border rounded-xl p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Faze projekta</h2>

        <div v-if="!canSubmit" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
          <p class="text-sm text-yellow-700">
            <i class="pi pi-info-circle mr-2" />
            Projekt mora biti prihvaćen od strane mentora prije nego što možeš predavati faze.
          </p>
        </div>

        <div class="flex flex-col gap-3">
          <div
            v-for="item in phases"
            :key="item.phase.id"
            class="border border-gray-200 rounded-xl p-4"
            :class="{ 'border-green-200 bg-green-50': item.submission?.status === 'approved' }"
          >
            <div class="flex items-start gap-3">

              <i :class="phaseStatusIcon(item.submission?.status)" class="text-xl mt-1" />

              <div class="flex-1">
                <div class="flex items-center justify-between">
                  <div>
                    <span class="text-sm text-gray-400 font-medium">Faza {{ item.phase.step_number }}</span>
                    <h4 class="font-semibold text-gray-800">{{ item.phase.name }}</h4>
                    <p class="text-sm text-gray-500 mt-1">{{ item.phase.description }}</p>
                  </div>
                  <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">{{ phaseStatusLabel(item.submission?.status) }}</span>
                    <Button
                      v-if="canSubmit && item.submission?.status !== 'approved'"
                      :label="item.submission ? 'Ažuriraj' : 'Predaj'"
                      icon="pi pi-upload"
                      size="small"
                      :severity="item.submission?.status === 'rejected' ? 'warn' : 'primary'"
                      @click="openSubmitForm(item.phase.id)"
                    />
                  </div>
                </div>


                <div v-if="item.submission?.submission_text" class="mt-3 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                  <strong>Tvoja predaja:</strong> {{ item.submission.submission_text }}
                </div>


                <div v-if="item.submission?.mentor_feedback" class="mt-2 p-3 bg-blue-50 rounded-lg text-sm text-blue-700">
                  <strong>Feedback mentora:</strong> {{ item.submission.mentor_feedback }}
                </div>
              </div>
            </div>


            <div v-if="activePhaseId === item.phase.id" class="mt-4 border-t border-gray-200 pt-4">
              <Textarea
                v-model="submissionText"
                :placeholder="`Opiši što si napravio/la za fazu: ${item.phase.name}`"
                rows="4"
                class="w-full mb-3"
                autofocus
              />
              <div class="flex gap-2 justify-end">
                <Button label="Odustani" severity="secondary" size="small" @click="activePhaseId = null" />
                <Button
                  label="Predaj fazu"
                  icon="pi pi-send"
                  size="small"
                  :loading="submitting"
                  :disabled="!submissionText.trim()"
                  @click="submitPhase"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
