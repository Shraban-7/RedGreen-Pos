export default [
  {
    path: '/categories',
    name: 'categories.list',
    component: () => import('@/pages/categories/CategoryList.vue'),
    meta: { auth: true }
  }
];
