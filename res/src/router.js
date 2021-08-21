import Vue from 'vue'
import VueRouter from 'vue-router'
import PopupManager from './components/PopupManager.vue'

Vue.use(VueRouter)

export default new VueRouter({
    routes: [
        {
            path: '/',
            name: 'beaver-popups-manager',
            component: PopupManager
        }
    ]
})
