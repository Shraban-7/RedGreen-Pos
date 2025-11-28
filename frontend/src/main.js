import { createApp } from 'vue'
import App from './App.vue'
import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura';
import './main.css'
import router from './routes/router'

const app = createApp(App)
app.use(router)
app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: 'light',
            cssLayer: false
        }
    }
});
app.mount('#app')
