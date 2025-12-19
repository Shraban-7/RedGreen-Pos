import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import LoginView from '@/pages/Login.vue'
import HomeView from '@/components/HomeView.vue'
import BaseLayout from '@/layouts/BaseLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'

import { menuLinks } from '@/config/sidebarLinks'
import { generateDynamicRoutes } from './autoRoutes'

const protectedChildren = [
  {
    path: '',
    name: 'Home',
    component: HomeView,
    meta: { auth: true },
  },
  ...generateDynamicRoutes(menuLinks).map(route => ({
    ...route,
    meta: { auth: true },
  })),
]

const routes = [
  // AUTH (PUBLIC)
  {
    path: '/login',
    component: AuthLayout,
    children: [
      {
        path: '',
        name: 'Login',
        component: LoginView,
      },
    ],
  },

  // APP (PROTECTED)
  {
    path: '/',
    component: BaseLayout,
    children: protectedChildren,
  },

  // FALLBACK
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (auth.token && !auth.user) {
    await auth.fetchUser()
  }

  if (to.meta.auth && !auth.isAuthenticated) {
    return { name: 'Login' }
  }

  if (to.name === 'Login' && auth.isAuthenticated) {
    return { name: 'Home' }
  }
})

export default router
