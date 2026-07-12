export default [
    {
        path: '/pos/create',
        name: 'pos.create',
        component: () => import('@/pages/pos/Create.vue'),
        meta: { auth: true }
    },
    {
        path: '/pos/sales',
        name: 'pos.sales',
        component: () => import('@/pages/pos/Sales.vue'),
        meta: { auth: true }
    },
    {
        path: '/pos/sales/:id',
        name: 'pos.saleDetail',
        component: () => import('@/pages/pos/SaleDetail.vue'),
        meta: { auth: true }
    }
];
