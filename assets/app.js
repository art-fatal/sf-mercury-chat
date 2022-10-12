/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import './styles/main.css';

// start the Stimulus application
import './bootstrap';

import { createApp } from 'vue';
import VueRouter from 'vue-router'
import store from "./modules/store";
import App from './components/App';
import Blank from "./components/Right/Blank";
import Right from "./components/Right/Right";

const app = createApp(App);
app.use(router);
app.use(store);

app.mount('#app')
//
// Vue.use(VueRouter)
//
// const routes = [
//     {
//         name: 'blank',
//         path: "/",
//         component: Blank
//     },
//     {
//         name: 'conversation',
//         path: "/conversation/:id",
//         component: Right
//     },
// ]
//
// const router = new VueRouter({
//     mode: "abstract",
//     routes,
// })
//
// Vue.prototype.$store = store
//
// const app = new Vue({
//     store,
//     router,
//     render: h => h(App)
// }).$mount('#app')
//
// router.replace('/')