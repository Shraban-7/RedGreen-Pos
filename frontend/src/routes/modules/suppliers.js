export default [
  {
    path: '/suppliers',
    name: 'suppliers.list',
    component: () => import('@/pages/suppliers/SupplierList.vue'),
    meta: { auth: true }
  }
];
