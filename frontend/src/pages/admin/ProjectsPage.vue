<script setup lang="ts">
import { ref, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import api from '@/lib/axios'
import { useToast } from 'primevue/usetoast'

const toast = useToast()

const projects = ref<any[]>([])
const mentors = ref<any[]>([])
const loading = ref(true)
const showAssignDialog = ref(false)

const selectedProject = ref<any>(null)
const selectedMentorId = ref<number | null>(null)
const assigning = ref(false)

onMounted(async () => {
  const [projectsRes, usersRes] = await Promise.all([
    api.get('/admin/projects').catch(() => ({ data: [] })),
    api.get('/admin/users').catch(() => ({ data: [] })),
  ])
  projects.value = projectsRes.data
  mentors.value = usersRes.data.filter((u: any) => u.role === 'mentor')
  loading.value = false
})

function openAssignMentor(project: any) {
  selectedProject.value = project
  selectedMentorId.value = project.mentor_id || null
  showAssignDialog.value = true
}

async function assignMentor() {
  if (!selectedProject.value || !selectedMentorId.value) return
  assigning.value = true

  try {
    await api.put(`/admin/projects/${selectedProject.value.id}/assign-mentor`, {
      mentor_id: selectedMentorId.value,
    })
    toast.add({ severity: 'success', summary: 'Uspjeh!', detail: 'Mentor je dodijeljen projektu.', life: 3000 })
    selectedProject.value = null
    showAssignDialog.value = false

    const response = await api.get('/admin/projects')
    projects.value = response.data
  } catch {
    toast.add({ severity: 'error', summary: 'Greška', detail: 'Dodjela mentora nije uspjela.', life: 3000 })
  } finally {
    assigning.value = false
  }
}

function statusSeverity(status: string) {
  if (status === 'accepted') return 'success'
  if (status === 'rejected') return 'danger'
  return 'warn'
}

function statusLabel(status: string) {
  if (status === 'accepted') return 'Prihvaćeno'
  if (status === 'rejected') return 'Odbijeno'
  return 'Na čekanju'
}
</script>

<template>
  <MainLayout>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Svi projekti</h1>

    <div v-if="loading" class="flex justify-center py-20">
      <ProgressSpinner />
    </div>

    <div v-else class="bg-white border rounded-xl shadow-sm overflow-hidden">
      <DataTable :value="projects" stripedRows>
        <Column field="title" header="Naziv" sortable>
          <template #body="{ data }">
            <span class="font-medium">{{ data.title }}</span>
          </template>
        </Column>

        <Column header="Student">
          <template #body="{ data }">
            {{ data.student?.name || '—' }}
          </template>
        </Column>

        <Column header="Mentor">
          <template #body="{ data }">
            <span v-if="data.mentor">{{ data.mentor.name }}</span>
            <span v-else class="text-gray-400 italic">Nije dodijeljen</span>
          </template>
        </Column>

        <Column field="status" header="Status" sortable>
          <template #body="{ data }">
            <Tag :severity="statusSeverity(data.status)" :value="statusLabel(data.status)" />
          </template>
        </Column>

        <Column header="Napredak">
          <template #body="{ data }">
            <div class="flex items-center gap-2 min-w-32">
              <ProgressBar :value="data.progress_percentage" class="flex-1" />
              <span class="text-sm text-gray-500 w-10">{{ data.progress_percentage }}%</span>
            </div>
          </template>
        </Column>

        <Column header="Akcije">
          <template #body="{ data }">
            <Button
              label="Dodjeli mentora"
              icon="pi pi-user-edit"
              size="small"
              severity="info"
              outlined
              @click="openAssignMentor(data)"
            />
          </template>
        </Column>
      </DataTable>
    </div>


    <Dialog
      v-model:visible="showAssignDialog"
      header="Dodjeli mentora projektu"
      modal
      class="w-full max-w-md"
    >
      <div v-if="selectedProject" class="flex flex-col gap-4">
        <p class="text-gray-600">Projekt: <strong>{{ selectedProject.title }}</strong></p>
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Odaberi mentora</label>
          <Select
            v-model="selectedMentorId"
            :options="mentors"
            optionLabel="name"
            optionValue="id"
            placeholder="Odaberi mentora..."
            class="w-full"
          />
        </div>
      </div>

      <template #footer>
        <Button label="Odustani" severity="secondary" @click="showAssignDialog = false; selectedProject = null" />
        <Button
          label="Dodjeli"
          icon="pi pi-check"
          :loading="assigning"
          :disabled="!selectedMentorId"
          @click="assignMentor"
        />
      </template>
    </Dialog>
  </MainLayout>
</template>
