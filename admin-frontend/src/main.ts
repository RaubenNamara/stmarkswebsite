import { createApp } from 'vue'
import { createPinia } from 'pinia'
// Self-hosted font (no request to Google) - the same Plus Jakarta Sans as the public website.
import '@fontsource-variable/plus-jakarta-sans/wght.css'
import './style.css'
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
