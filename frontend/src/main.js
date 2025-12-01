import { createApp } from 'vue'
import App from './App.vue'
import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura';
import './main.css'
import router from './routes/router'
import { library } from '@fortawesome/fontawesome-svg-core';
import { faUserSecret, faCoffee } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import ToastService from 'primevue/toastservice'
import Toast from 'primevue/toast';

library.add(faUserSecret, faCoffee);

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

app.use(ToastService) 
app.component('Toast', Toast)
app.component('font-awesome-icon', FontAwesomeIcon);

app.mount('#app')
