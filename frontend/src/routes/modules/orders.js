export default [
  {
    path: '/orders',
    name: 'orders.list',
    component: () => import('@/pages/orders/OrderList.vue'),
    meta: { auth: true }
  }
];
