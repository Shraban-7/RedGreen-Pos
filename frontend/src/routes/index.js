import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

import BaseLayout from '@/layouts/BaseLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';

// Route modules
import dashboard from './modules/dashboard';
import products from './modules/products';
import categories from './modules/categories';
import brands from './modules/brands';
import orders from './modules/orders';
import suppliers from './modules/suppliers';
// import reports from './modules/reports';
import auth from './modules/auth';

const routes = [
  // Public Auth Layout
  {
    path: '/login',
    component: AuthLayout,
    children: auth
  },

  // Protected Base Layout
  {
    path: '/',
    component: BaseLayout,
    children: [
      ...dashboard,
      ...products,
      ...categories,
      ...brands,
      ...orders,
      ...suppliers,
    //   ...reports
    ]
  },

  // Fallback
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Auth Guard
router.beforeEach(async (to) => {
  const auth = useAuthStore();

  if (auth.token && !auth.user) {
    await auth.fetchUser();
  }

  if (to.meta.auth && !auth.isAuthenticated) {
    return '/login';
  }

  if (to.name === 'login' && auth.isAuthenticated) {
    return '/dashboard';
  }
});

export default router;
