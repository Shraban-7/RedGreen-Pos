export default [
  {
    path: '/brands',
    name: 'brands.list',
    component: () => import('@/pages/brands/BrandList.vue'),
    meta: { auth: true }
  }
];
