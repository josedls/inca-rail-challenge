import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AppLayout from '../../layouts/AppLayout.vue'
import CompetitionsView from '../../views/football/CompetitionsView.vue'
import CompetitionDetailView from '../../views/football/CompetitionDetailView.vue'
import TeamsView from '../../views/football/TeamsView.vue'
import TeamDetailView from '../../views/football/TeamDetailView.vue'
import PlayersView from '../../views/football/PlayersView.vue'
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
        {
          path: 'competitions',
          name: 'competitions',
          component: CompetitionsView,
        },
        {
          path: 'competitions/:competition',
          name: 'competition-detail',
          component: CompetitionDetailView,
        },
        {
          path: 'teams',
          name: 'teams',
          component: TeamsView,
        },
        {
          path: 'teams/:team',
          name: 'team-detail',
          component: TeamDetailView,
        },
        {
          path: 'players',
          name: 'players',
          component: PlayersView,
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
    return {
      name: 'login',
      query: { redirect: to.fullPath },
    }
  }

  if (to.name === 'login' && authStore.isAuthenticated) {
    return { name: 'competitions' }
  }

  return true
})
