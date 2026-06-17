<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import api from '@/lib/axios'

const router = useRouter()

const myProjects = ref<any[]>([])
const unassignedProjects = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const response = await api.get('/mentor/projects')
    myProjects.value = response.data.my_projects
    unassignedProjects.value = response.data.unassigned_projects
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
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Mentor Dashboard</h1>

    <div v-if="loading" class="flex justify-center py-20">
      <ProgressSpinner />
    </div>

    <div v-else>

      <section class="mb-8">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">
          Moji projekti ({{ myProjects.length }})
        </h2>

        <div v-if="myProjects.length === 0" class="text-center py-10 text-gray-400">
          <i class="pi pi-folder-open text-4xl mb-3 block" />
          <p>Nemaš dodijeljenih projekata.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
          <div
            v-for="project in myProjects"
            :key="project.id"
            class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
            @click="router.push(`/mentor/projects/${project.id}`)"
          >
            <div class="flex items-start justify-between mb-2">
              <h3 class="font-semibold text-gray-800">{{ project.title }}</h3>
              <Tag :severity="statusSeverity(project.status)" :value="statusLabel(project.status)" />
            </div>
            <p class="text-sm text-gray-500 mb-3">
              <i class="pi pi-user mr-1" /> {{ project.student?.name }}
            </p>
            <div class="flex items-center justify-between text-sm text-gray-500">
              <span>Napredak</span>
              <span>{{ project.progress_percentage }}%</span>
            </div>
            <ProgressBar :value="project.progress_percentage" class="mt-1" />
          </div>
        </div>
      </section>


      <section v-if="unassignedProjects.length > 0">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">
          Projekti bez mentora ({{ unassignedProjects.length }})
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
          <div
            v-for="project in unassignedProjects"
            :key="project.id"
            class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm"
          >
            <h3 class="font-semibold text-gray-800 mb-1">{{ project.title }}</h3>
            <p class="text-sm text-gray-500 mb-3">
              <i class="pi pi-user mr-1" /> {{ project.student?.name }}
            </p>
            <Tag severity="warn" value="Na čekanju" />
          </div>
        </div>
      </section>
    </div>
  </MainLayout>
</template>
