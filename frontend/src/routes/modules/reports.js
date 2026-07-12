export default [
  {
    path: '/reports/sales',
    name: 'reports.sales',
    component: () => import('@/pages/reports/Sales.vue'),
    meta: { auth: true }
  },
  {
    path: '/reports/customers',
    name: 'reports.customers',
    component: () => import('@/pages/reports/Customers.vue'),
    meta: { auth: true }
  },
  {
    path: '/reports/expenses',
    name: 'reports.expenses',
    component: () => import('@/pages/reports/Expenses.vue'),
    meta: { auth: true }
  },
  {
    path: '/reports/overall',
    name: 'reports.overall',
    component: () => import('@/pages/reports/Overall.vue'),
    meta: { auth: true }
  }
];