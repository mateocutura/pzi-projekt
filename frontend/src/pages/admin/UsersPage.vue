<script setup lang="ts">
import { ref, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import api from '@/lib/axios'
import { useToast } from 'primevue/usetoast'

const toast = useToast()

const users = ref<any[]>([])
const loading = ref(true)
const editingUser = ref<any>(null)
const updatingRole = ref(false)

const roleOptions = [
  { label: 'Student', value: 'student' },
  { label: 'Mentor', value: 'mentor' },
  { label: 'Admin', value: 'admin' },
]

onMounted(async () => {
  await loadUsers()
})

async function loadUsers() {
  loading.value = true
  try {
    const response = await api.get('/admin/users')
    users.value = response.data
  } finally {
    loading.value = false
  }
}

function startEditRole(user: any) {
  editingUser.value = { ...user, newRole: user.role }
}

async function saveRole() {
  if (!editingUser.value) return
  updatingRole.value = true

  try {
    await api.put(`/admin/users/${editingUser.value.id}`, {
      role: editingUser.value.newRole,
    })
    toast.add({ severity: 'success', summary: 'Uspjeh!', detail: 'Uloga je promijenjena.', life: 3000 })
    editingUser.value = null
    await loadUsers()
  } catch {
    toast.add({ severity: 'error', summary: 'Greška', detail: 'Promjena uloge nije uspjela.', life: 3000 })
  } finally {
    updatingRole.value = false
  }
}

function roleSeverity(role: string) {
  if (role === 'admin') return 'danger'
  if (role === 'mentor') return 'info'
  return 'secondary'
}

function roleLabel(role: string) {
  if (role === 'admin') return 'Admin'
  if (role === 'mentor') return 'Mentor'
  return 'Student'
}
</script>

<template>
  <MainLayout>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Upravljanje korisnicima</h1>

    <div v-if="loading" class="flex justify-center py-20">
      <ProgressSpinner />
    </div>

    <div v-else class="bg-white border rounded-xl shadow-sm overflow-hidden">
      <DataTable :value="users" stripedRows>
        <Column field="name" header="Ime" sortable>
          <template #body="{ data }">
            <div class="flex items-center gap-2">
              <i class="pi pi-user text-gray-400" />
              <span class="font-medium">{{ data.name }}</span>
            </div>
          </template>
        </Column>

        <Column field="email" header="Email" sortable />

        <Column field="role" header="Uloga" sortable>
          <template #body="{ data }">
            <div v-if="editingUser?.id === data.id" class="flex items-center gap-2">
              <Select
                v-model="editingUser.newRole"
                :options="roleOptions"
                optionLabel="label"
                optionValue="value"
                class="w-32"
              />
              <Button icon="pi pi-check" severity="success" size="small" :loading="updatingRole" @click="saveRole" />
              <Button icon="pi pi-times" severity="secondary" size="small" @click="editingUser = null" />
            </div>
            <div v-else class="flex items-center gap-2">
              <Tag :severity="roleSeverity(data.role)" :value="roleLabel(data.role)" />
              <Button icon="pi pi-pencil" text size="small" @click="startEditRole(data)" />
            </div>
          </template>
        </Column>

        <Column header="Projekti">
          <template #body="{ data }">
            <span class="text-gray-600">
              <span v-if="data.role === 'student'">{{ data.projects_count }} projekt(a)</span>
              <span v-else-if="data.role === 'mentor'">{{ data.mentor_projects_count }} dodijeljenih</span>
              <span v-else>—</span>
            </span>
          </template>
        </Column>

        <Column field="created_at" header="Kreiran" sortable>
          <template #body="{ data }">
            {{ new Date(data.created_at).toLocaleDateString('hr') }}
          </template>
        </Column>
      </DataTable>
    </div>
  </MainLayout>
</template>
