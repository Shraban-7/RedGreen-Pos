import HomeView from '@/components/HomeView.vue'
import { createRouter, createWebHistory } from 'vue-router'
import {menuLinks} from '@/config/sidebarLinks'
import { generateDynamicRoutes } from "./autoRoutes";
import BaseLayout from '@/layouts/BaseLayout.vue'

const childRoutes = [
    {
        path: "",
        name: "Home",
        component: HomeView,
    },
    ...generateDynamicRoutes(menuLinks) 
];

const routes = [
    {
        path: "/",
        component: BaseLayout,
        children: childRoutes,
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router