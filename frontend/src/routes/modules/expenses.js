export default [
  {
    path: '/expenses',
    name: 'expenses.list',
    component: () => import('@/pages/expenses/Index.vue'),
    meta: { auth: true }
  },
  {
    path: '/expenses/create',
    name: 'expenses.create',
    component: () => import('@/pages/expenses/Form.vue'),
    meta: { auth: true }
  },
  {
    path: '/expenses/:id/edit',
    name: 'expenses.edit',
    component: () => import('@/pages/expenses/Form.vue'),
    meta: { auth: true }
  }
];