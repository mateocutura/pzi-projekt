<script setup lang="ts">
import { ref, onMounted } from 'vue'
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

const showStatusDialog = ref(false)
const selectedStatus = ref<'accepted' | 'rejected'>('accepted')
const rejectionReason = ref('')
const updatingStatus = ref(false)

const activeSubmission = ref<any>(null)
const feedbackText = ref('')
const reviewingSubmission = ref(false)

onMounted(async () => {
  await loadProject()
})

async function loadProject() {
  loading.value = true
  try {
    const response = await api.get(`/mentor/projects/${route.params.id}`)
    project.value = response.data.project
    phases.value = response.data.phases
  } catch {
    toast.add({ severity: 'error', summary: 'Greška', detail: 'Projekt nije pronađen.', life: 3000 })
    router.push('/mentor/dashboard')
  } finally {
    loading.value = false
  }
}

function openStatusDialog(status: 'accepted' | 'rejected') {
  selectedStatus.value = status
  rejectionReason.value = ''
  showStatusDialog.value = true
}

async function updateProjectStatus() {
  updatingStatus.value = true
  try {
    await api.put(`/mentor/projects/${project.value.id}/status`, {
      status: selectedStatus.value,
      rejection_reason: rejectionReason.value || null,
    })
    toast.add({
      severity: selectedStatus.value === 'accepted' ? 'success' : 'warn',
      summary: selectedStatus.value === 'accepted' ? 'Prihvaćeno' : 'Odbijeno',
      detail: `Projekt je ${selectedStatus.value === 'accepted' ? 'prihvaćen' : 'odbijen'}.`,
      life: 3000,
    })
    showStatusDialog.value = false
    await loadProject()
  } catch (error: any) {
    toast.add({ severity: 'error', summary: 'Greška', detail: error.response?.data?.message || 'Greška.', life: 3000 })
  } finally {
    updatingStatus.value = false
  }
}

function openReviewSubmission(submission: any) {
  activeSubmission.value = submission
  feedbackText.value = ''
}

async function reviewSubmission(status: 'approved' | 'rejected') {
  if (!activeSubmission.value) return
  reviewingSubmission.value = true

  try {
    await api.put(`/mentor/projects/${project.value.id}/submissions/${activeSubmission.value.id}`, {
      status,
      mentor_feedback: feedbackText.value || null,
    })
    toast.add({
      severity: status === 'approved' ? 'success' : 'warn',
      summary: status === 'approved' ? 'Odobreno' : 'Odbijeno',
      detail: `Faza je ${status === 'approved' ? 'odobrena' : 'odbijena'}.`,
      life: 3000,
    })
    activeSubmission.value = null
    await loadProject()
  } catch (error: any) {
    toast.add({ severity: 'error', summary: 'Greška', detail: error.response?.data?.message || 'Greška.', life: 3000 })
  } finally {
    reviewingSubmission.value = false
  }
}

function phaseStatusIcon(status: string | undefined) {
  if (status === 'approved') return 'pi pi-check-circle text-green-500'
  if (status === 'submitted') return 'pi pi-clock text-yellow-500'
  if (status === 'rejected') return 'pi pi-times-circle text-red-500'
  return 'pi pi-circle text-gray-300'
}
</script>

<template>
  <MainLayout>
    <div v-if="loading" class="flex justify-center py-20">
      <ProgressSpinner />
    </div>

    <div v-else-if="project">

      <div class="flex items-center gap-3 mb-6">
        <Button icon="pi pi-arrow-left" severity="secondary" text @click="router.push('/mentor/dashboard')" />
        <div class="flex-1">
          <h1 class="text-2xl font-bold text-gray-800">{{ project.title }}</h1>
          <p class="text-sm text-gray-500 mt-1">
            <i class="pi pi-user mr-1" /> Student: {{ project.student?.name }} ({{ project.student?.email }})
          </p>
        </div>


        <div v-if="project.status === 'pending'" class="flex gap-2">
          <Button
            label="Prihvati projekt"
            icon="pi pi-check"
            severity="success"
            @click="openStatusDialog('accepted')"
          />
          <Button
            label="Odbij projekt"
            icon="pi pi-times"
            severity="danger"
            outlined
            @click="openStatusDialog('rejected')"
          />
        </div>

        <Tag
          v-else
          :severity="project.status === 'accepted' ? 'success' : 'danger'"
          :value="project.status === 'accepted' ? 'Prihvaćeno' : 'Odbijeno'"
          class="text-base px-3 py-1"
        />
      </div>


      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border rounded-xl p-4 shadow-sm col-span-2">
          <h3 class="font-semibold text-gray-700 mb-2">Opis</h3>
          <p class="text-gray-600">{{ project.description }}</p>
          <h3 class="font-semibold text-gray-700 mt-4 mb-2">Vizija</h3>
          <p class="text-gray-600">{{ project.vision }}</p>
          <div v-if="project.github_url" class="mt-3">
            <a :href="project.github_url" target="_blank" class="text-indigo-600 flex items-center gap-2 hover:underline">
              <i class="pi pi-github" /> GitHub repozitorij
            </a>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 shadow-sm">
          <h3 class="font-semibold text-gray-700 mb-3">Napredak</h3>
          <div class="text-4xl font-bold text-indigo-600 text-center my-4">{{ project.progress_percentage }}%</div>
          <ProgressBar :value="project.progress_percentage" />
          <p class="text-sm text-gray-500 text-center mt-2">
            {{ Math.round(project.progress_percentage / 10) }} / 10 faza završeno
          </p>
        </div>
      </div>


      <div class="bg-white border rounded-xl p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Faze i predaje studenta</h2>

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
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <span class="text-sm text-gray-400 font-medium">Faza {{ item.phase.step_number }}</span>
                    <h4 class="font-semibold text-gray-800">{{ item.phase.name }}</h4>
                  </div>

                  <Button
                    v-if="item.submission?.status === 'submitted'"
                    label="Pregled"
                    icon="pi pi-eye"
                    size="small"
                    severity="info"
                    @click="openReviewSubmission(item.submission)"
                  />
                </div>


                <div v-if="item.submission?.submission_text" class="mt-3 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                  <strong>Predaja studenta:</strong> {{ item.submission.submission_text }}
                </div>


                <div v-if="item.submission?.mentor_feedback" class="mt-2 p-3 bg-blue-50 rounded-lg text-sm text-blue-700">
                  <strong>Tvoj feedback:</strong> {{ item.submission.mentor_feedback }}
                </div>


                <div v-if="activeSubmission?.id === item.submission?.id" class="mt-4 border-t pt-4">
                  <Textarea
                    v-model="feedbackText"
                    placeholder="Opcionalni feedback studentu..."
                    rows="3"
                    class="w-full mb-3"
                    autofocus
                  />
                  <div class="flex gap-2 justify-end">
                    <Button label="Odustani" severity="secondary" size="small" @click="activeSubmission = null" />
                    <Button
                      label="Odbij"
                      icon="pi pi-times"
                      severity="danger"
                      size="small"
                      outlined
                      :loading="reviewingSubmission"
                      @click="reviewSubmission('rejected')"
                    />
                    <Button
                      label="Odobri"
                      icon="pi pi-check"
                      severity="success"
                      size="small"
                      :loading="reviewingSubmission"
                      @click="reviewSubmission('approved')"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


    <Dialog
      v-model:visible="showStatusDialog"
      :header="selectedStatus === 'accepted' ? 'Prihvati projekt' : 'Odbij projekt'"
      modal
      class="w-full max-w-md"
    >
      <div v-if="selectedStatus === 'rejected'" class="mb-4">
        <label class="block font-medium text-gray-700 mb-2">Razlog odbijanja *</label>
        <Textarea
          v-model="rejectionReason"
          placeholder="Objasni studentu zašto je projekt odbijen..."
          rows="4"
          class="w-full"
          autofocus
        />
      </div>
      <p v-else class="text-gray-600">
        Jesi li siguran/sigurna da želiš prihvatiti ovaj projekt? Student će moći početi predavati faze.
      </p>

      <template #footer>
        <Button label="Odustani" severity="secondary" @click="showStatusDialog = false" />
        <Button
          :label="selectedStatus === 'accepted' ? 'Prihvati' : 'Odbij'"
          :severity="selectedStatus === 'accepted' ? 'success' : 'danger'"
          :loading="updatingStatus"
          :disabled="selectedStatus === 'rejected' && !rejectionReason.trim()"
          @click="updateProjectStatus"
        />
      </template>
    </Dialog>
  </MainLayout>
</template>
