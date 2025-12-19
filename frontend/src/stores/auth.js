import { defineStore } from "pinia"
import api from "@/config/axios"

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        loading: false
    }),

    getters: {
        isAuthenticated: (state) => !!state.token
    },

    actions: {
        setAuth(data) {
            this.user = data.user
            this.token = data.token

            localStorage.setItem('token', data.token)
            api.defaults.headers.common['Authorization'] = `Bearer ${data.token}`
        },

        async login(form) {
            this.loading = true
            try {
                const res = await api.post('/login', form)
                this.setAuth(res.data.data)
                return res
            } finally {
                this.loading = false
            }
        },

        async register(form) {
            this.loading = true
            try {
                const res = await api.post('/register', form)
                this.setAuth(res.data.data)
                return res
            } finally {
                this.loading = false
            }
        },

        async fetchUser() {
            if (!this.token) return

            api.defaults.headers.common['Authorization'] =
                `Bearer ${this.token}`

            try {
                const res = await api.get('/user')
                this.user = res.data.data
            } catch (e) {
                this.logout()
            }
        },

        logout() {
            this.user = null
            this.token = null
            localStorage.removeItem('token')
            delete api.defaults.headers.common['Authorization']
        }
    }
})
