export default [
  {
    path: '/orders',
    name: 'orders.list',
    component: () => import('@/pages/orders/OrderList.vue'),
    meta: { auth: true }
  },
  {
    path: '/orders/:id',
    name: 'orders.detail',
    component: () => import('@/pages/orders/OrderDetail.vue'),
    meta: { auth: true }
  }
];