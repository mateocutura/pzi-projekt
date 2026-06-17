import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [

    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/LoginPage.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/pages/RegisterPage.vue'),
      meta: { guestOnly: true },
    },


    {
      path: '/',
      redirect: '/dashboard',
    },


    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/pages/student/DashboardPage.vue'),
      meta: { requiresAuth: true, role: 'student' },
    },
    {
      path: '/projects/create',
      name: 'project-create',
      component: () => import('@/pages/student/CreateProjectPage.vue'),
      meta: { requiresAuth: true, role: 'student' },
    },
    {
      path: '/projects/:id',
      name: 'project-detail',
      component: () => import('@/pages/student/ProjectDetailPage.vue'),
      meta: { requiresAuth: true, role: 'student' },
    },


    {
      path: '/mentor/dashboard',
      name: 'mentor-dashboard',
      component: () => import('@/pages/mentor/DashboardPage.vue'),
      meta: { requiresAuth: true, role: 'mentor' },
    },
    {
      path: '/mentor/projects/:id',
      name: 'mentor-project-detail',
      component: () => import('@/pages/mentor/ProjectDetailPage.vue'),
      meta: { requiresAuth: true, role: 'mentor' },
    },
    {
      path: '/mentor/statistics',
      name: 'mentor-statistics',
      component: () => import('@/pages/mentor/StatisticsPage.vue'),
      meta: { requiresAuth: true, role: 'mentor' },
    },


    {
      path: '/admin/users',
      name: 'admin-users',
      component: () => import('@/pages/admin/UsersPage.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/projects',
      name: 'admin-projects',
      component: () => import('@/pages/admin/ProjectsPage.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()


  if (auth.token && !auth.user) {
    await auth.fetchUser()
  }


  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return { name: 'login' }
  }


  if (to.meta.guestOnly && auth.isLoggedIn) {
    return redirectByRole(auth.user!.role)
  }


  if (to.meta.role && auth.user?.role !== to.meta.role) {
    return redirectByRole(auth.user!.role)
  }
})

function redirectByRole(role: string) {
  if (role === 'mentor') return { name: 'mentor-dashboard' }
  if (role === 'admin') return { name: 'admin-users' }
  return { name: 'dashboard' }
}

export default router
