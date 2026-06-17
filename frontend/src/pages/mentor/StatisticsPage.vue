<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import api from '@/lib/axios'

const stats = ref<any>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const response = await api.get('/statistics')
    stats.value = response.data
  } finally {
    loading.value = false
  }
})

const pieChartData = computed(() => {
  if (!stats.value) return undefined
  return {
    labels: ['Na čekanju', 'Prihvaćeni', 'Odbijeni'],
    datasets: [
      {
        data: [stats.value.pending_count, stats.value.accepted_count, stats.value.rejected_count],
        backgroundColor: ['#f59e0b', '#22c55e', '#ef4444'],
      },
    ],
  } as object
})

const barChartData = computed(() => {
  if (!stats.value) return undefined
  return {
    labels: stats.value.progress_distribution.map((d: any) => `${d.approved_phases} faza`),
    datasets: [
      {
        label: 'Broj projekata',
        data: stats.value.progress_distribution.map((d: any) => d.count),
        backgroundColor: '#6366f1',
      },
    ],
  } as object
})

const chartOptions = {
  responsive: true,
  plugins: { legend: { position: 'bottom' } },
}
</script>

<template>
  <MainLayout>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Statistike</h1>

    <div v-if="loading" class="flex justify-center py-20">
      <ProgressSpinner />
    </div>

    <div v-else-if="stats">

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border rounded-xl p-5 shadow-sm text-center">
          <div class="text-3xl font-bold text-indigo-600">{{ stats.total_projects }}</div>
          <div class="text-sm text-gray-500 mt-1">Ukupno projekata</div>
        </div>
        <div class="bg-white border rounded-xl p-5 shadow-sm text-center">
          <div class="text-3xl font-bold text-green-500">{{ stats.accepted_count }}</div>
          <div class="text-sm text-gray-500 mt-1">Prihvaćeni</div>
        </div>
        <div class="bg-white border rounded-xl p-5 shadow-sm text-center">
          <div class="text-3xl font-bold text-yellow-500">{{ stats.pending_count }}</div>
          <div class="text-sm text-gray-500 mt-1">Na čekanju</div>
        </div>
        <div class="bg-white border rounded-xl p-5 shadow-sm text-center">
          <div class="text-3xl font-bold text-emerald-600">{{ stats.completed_count }}</div>
          <div class="text-sm text-gray-500 mt-1">Završeni (100%)</div>
        </div>
      </div>


      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-white border rounded-xl p-6 shadow-sm">
          <h3 class="font-semibold text-gray-700 mb-4">Status projekata</h3>
          <div v-if="stats.total_projects > 0" class="max-w-xs mx-auto">
            <Chart type="pie" :data="pieChartData" :options="chartOptions" />
          </div>
          <p v-else class="text-center text-gray-400 py-8">Nema podataka za prikaz.</p>
        </div>


        <div class="bg-white border rounded-xl p-6 shadow-sm">
          <h3 class="font-semibold text-gray-700 mb-4">Gdje su studenti stali?</h3>
          <div v-if="stats.progress_distribution.length > 0">
            <Chart type="bar" :data="barChartData" :options="{ ...chartOptions, plugins: { legend: { display: false } } }" />
          </div>
          <p v-else class="text-center text-gray-400 py-8">Nema podataka za prikaz.</p>
        </div>
      </div>


      <div class="bg-white border rounded-xl p-6 shadow-sm mt-6">
        <h3 class="font-semibold text-gray-700 mb-3">Opće informacije</h3>
        <div class="flex flex-wrap gap-6">
          <div>
            <span class="text-gray-500 text-sm">Studenti s projektima:</span>
            <span class="font-semibold ml-2 text-gray-800">{{ stats.students_with_projects }}</span>
          </div>
          <div>
            <span class="text-gray-500 text-sm">Odbijeni projekti:</span>
            <span class="font-semibold ml-2 text-red-500">{{ stats.rejected_count }}</span>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
