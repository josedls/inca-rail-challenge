import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AppLayout from '../../layouts/AppLayout.vue'
import HomeView from '../../views/HomeView.vue'
import LoginView from '../../views/auth/LoginView.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: AppLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          component: HomeView,
        },
      ],
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
  ],
})

router.beforeEach((to) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.name === 'login' && authStore.isAuthenticated) {
    return { name: 'home' }
  }

  return true
})
