<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import api from '@/lib/axios'

const router = useRouter()

interface Project {
  id: number
  title: string
  status: 'pending' | 'accepted' | 'rejected'
  rejection_reason: string | null
  progress_percentage: number
  created_at: string
}

const projects = ref<Project[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const response = await api.get('/projects')
    projects.value = response.data
  } finally {
    loading.value = false
  }
})

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
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Moji projekti</h1>
        <p class="text-sm text-gray-500 mt-0.5">Pregled svih tvojih prijavljenih projekata</p>
      </div>
      <Button
        label="Novi projekt"
        icon="pi pi-plus"
        @click="router.push('/projects/create')"
      />
    </div>


    <div v-if="loading" class="flex justify-center py-20">
      <ProgressSpinner />
    </div>


    <div v-else-if="projects.length === 0" class="flex flex-col items-center justify-center py-24 bg-white rounded-2xl border border-gray-200">
      <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
        <i class="pi pi-inbox text-3xl text-gray-400" />
      </div>
      <p class="text-gray-600 font-medium text-lg mb-1">Još nemaš nijedan projekt</p>
      <p class="text-gray-400 text-sm mb-6">Prijavi svoj završni rad i počni pratiti napredak</p>
      <Button
        label="Prijavi prvi projekt"
        icon="pi pi-plus"
        @click="router.push('/projects/create')"
      />
    </div>


    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div
        v-for="project in projects"
        :key="project.id"
        class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all cursor-pointer group"
        @click="router.push(`/projects/${project.id}`)"
      >
        <div class="flex items-start justify-between mb-4">
          <h3 class="font-semibold text-gray-900 text-base leading-tight group-hover:text-indigo-600 transition-colors pr-2">{{ project.title }}</h3>
          <Tag :severity="statusSeverity(project.status)" :value="statusLabel(project.status)" class="shrink-0" />
        </div>


        <div class="mb-4">
          <div class="flex justify-between text-xs text-gray-500 mb-1.5">
            <span>Napredak faza</span>
            <span class="font-medium">{{ project.progress_percentage }}%</span>
          </div>
          <ProgressBar :value="project.progress_percentage" style="height: 6px" />
        </div>


        <div v-if="project.rejection_reason" class="mt-3 p-3 bg-red-50 border border-red-100 rounded-lg">
          <p class="text-xs text-red-600 flex items-start gap-1.5">
            <i class="pi pi-exclamation-circle mt-0.5 shrink-0" />
            <span>{{ project.rejection_reason }}</span>
          </p>
        </div>

        <p class="text-xs text-gray-400 mt-3 flex items-center gap-1">
          <i class="pi pi-calendar text-xs" />
          {{ new Date(project.created_at).toLocaleDateString('hr') }}
        </p>
      </div>
    </div>
  </MainLayout>
</template>
