import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/axios'

interface User {
  id: number
  name: string
  email: string
  role: 'student' | 'mentor' | 'admin'
}

export const useAuthStore = defineStore('auth', () => {

  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))


  const isLoggedIn = computed(() => !!token.value && !!user.value)


  async function login(email: string, password: string) {
    const response = await api.post('/auth/login', { email, password })
    token.value = response.data.token
    user.value = response.data.user
    localStorage.setItem('token', response.data.token)
  }


  async function register(name: string, email: string, password: string, password_confirmation: string, role: string) {
    const response = await api.post('/auth/register', {
      name,
      email,
      password,
      password_confirmation,
      role,
    })
    token.value = response.data.token
    user.value = response.data.user
    localStorage.setItem('token', response.data.token)
  }


  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch {

    }
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }


  async function fetchUser() {
    if (!token.value) return
    try {
      const response = await api.get('/auth/me')
      user.value = response.data
    } catch {

      token.value = null
      localStorage.removeItem('token')
    }
  }

  return { user, token, isLoggedIn, login, register, logout, fetchUser }
})
