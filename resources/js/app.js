import './bootstrap';

import {createApp} from 'vue';
import router from './router'
import store from './store'
import App from './components/App.vue' 
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css"
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

createApp(App).use(router).use(store).use(VueSweetalert2).use(Toast, {
  transition: "Vue-Toastification__bounce",
  maxToasts: 20,
  newestOnTop: true,
  timeout: 1500,
}).mount('#app')
