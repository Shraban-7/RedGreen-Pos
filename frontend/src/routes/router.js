import HomeView from '@/components/HomeView.vue'
import TestView from '@/components/TestView.vue'
import { createRouter, createWebHistory } from 'vue-router'
import BaseLayout from '@/layouts/BaseLayout.vue'

const routes = [
    {
        path: '/',
        component:BaseLayout,
        children: [
            {
                path: '',
                component: HomeView,
                name: 'Home'
            },
            {
                path: 'test',
                component: TestView,
                name: 'Test'
            }
        ]
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router