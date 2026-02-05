export default [
  {
    path: '/products',
    name: 'products.list',
    component: () => import('@/pages/products/ProductList.vue'),
    meta: { auth: true }
  },
  {
    path: '/products/create',
    name: 'products.create',
    component: () => import('@/pages/products/ProductCreate.vue'),
    meta: { auth: true }
  },
  {
    path: '/products/:id/edit',
    name: 'products.edit',
    component: () => import('@/pages/products/ProductEdit.vue'),
    meta: { auth: true }
  }
];
