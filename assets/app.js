/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import './styles/main.css';

import Vue  from 'vue';
import VueRouter from 'vue-router'
import store from "./modules/store";
import App from './components/App';
import Blank from "./components/Right/Blank";
import Right from "./components/Right/Right";

Vue.use(VueRouter)

const routes = [
    {
        name: 'blank',
        path: "/",
        component: Blank
    },
    {
        name: 'conversation',
        path: "/conversation/:id",
        component: Right
    },
]

const router = new VueRouter({
    mode: "abstract",
    linkActiveClass: "active",
    routes,
})

store.commit("SET_USERNAME", document.getElementById("app").dataset.username)

const app = new Vue({
    store,
    router,
    render: h => h(App)
}).$mount('#app')

router.replace('/')