<script setup lang="ts">
import { computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useToast } from "primevue/usetoast";

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const toast = useToast();

const navItems = computed(() => {
  if (auth.user?.role === "student") {
    return [
      { label: "Dashboard", icon: "pi-home", to: "/dashboard" },
      { label: "Novi projekt", icon: "pi-plus-circle", to: "/projects/create" },
    ];
  }
  if (auth.user?.role === "mentor") {
    return [{ label: "Dashboard", icon: "pi-home", to: "/mentor/dashboard" }];
  }
  if (auth.user?.role === "admin") {
    return [
      { label: "Korisnici", icon: "pi-users", to: "/admin/users" },
      { label: "Projekti", icon: "pi-folder-open", to: "/admin/projects" },
    ];
  }
  return [];
});

const roleLabel = computed(() => {
  const roles: Record<string, string> = {
    student: "Student",
    mentor: "Mentor",
    admin: "Administrator",
  };
  return roles[auth.user?.role ?? ""] ?? auth.user?.role;
});

const roleBadgeClass = computed(() => {
  const classes: Record<string, string> = {
    student: "bg-indigo-100 text-indigo-700",
    mentor: "bg-emerald-100 text-emerald-700",
    admin: "bg-orange-100 text-orange-700",
  };
  return classes[auth.user?.role ?? ""] ?? "bg-gray-100 text-gray-600";
});

async function handleLogout() {
  await auth.logout();
  toast.add({
    severity: "info",
    summary: "Odjava",
    detail: "Uspješno ste se odjavili.",
    life: 3000,
  });
  router.push("/login");
}
</script>

<template>
  <div class="min-h-screen flex bg-gray-50">
    <aside
      class="w-64 bg-white border-r border-gray-200 flex flex-col shadow-sm shrink-0"
    >
      <div class="h-16 flex items-center px-6 border-b border-gray-200">
        <span class="text-lg font-bold text-gray-900">ProjectFlow</span>
      </div>

      <nav class="flex-1 px-3 py-4 space-y-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors text-sm font-medium"
          :class="{
            'bg-indigo-50 text-indigo-700 font-semibold':
              route.path === item.to,
          }"
        >
          <i
            :class="[
              'pi',
              item.icon,
              'text-base',
              route.path === item.to ? 'text-indigo-600' : 'text-gray-400',
            ]"
          />
          {{ item.label }}
        </RouterLink>
      </nav>

      <div class="p-4 border-t border-gray-200">
        <div class="flex items-center gap-3 mb-3">
          <div
            class="w-9 h-9 bg-indigo-100 rounded-full flex items-center justify-center shrink-0"
          >
            <i class="pi pi-user text-indigo-600 text-sm" />
          </div>
          <div class="min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">
              {{ auth.user?.name }}
            </p>
            <span
              :class="[
                'text-xs px-2 py-0.5 rounded-full font-medium',
                roleBadgeClass,
              ]"
            >
              {{ roleLabel }}
            </span>
          </div>
        </div>
        <button
          @click="handleLogout"
          class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
        >
          <i class="pi pi-sign-out text-sm" />
          Odjavi se
        </button>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
      <header
        class="h-16 bg-white border-b border-gray-200 flex items-center px-6"
      >
        <h1 class="text-lg font-semibold text-gray-900">
          {{
            navItems.find((n) => n.to === route.path)?.label ?? "ProjectFlow"
          }}
        </h1>
      </header>

      <main class="flex-1 p-6 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
