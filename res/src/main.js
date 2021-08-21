import Vue from 'vue'
import App from './App.vue'
import PopupShortcodeGenerator from './components/ShortcodeGenerator.vue'
import router from './router'
import store from './store'
import { sync } from 'vuex-router-sync'
import BootstrapVue from 'bootstrap-vue'
import VueClipboard from 'vue-clipboard2'

Vue.use(BootstrapVue)
Vue.use(VueClipboard)
sync(store, router)

const beaverPopupsManager = document.getElementById('beaver-popups-manager-app')
const beaverPopupsShortcodeGenerator = document.getElementById('beaver-popups-shortcode-generator-app')

if (beaverPopupsManager) {
	new Vue({
		el: beaverPopupsManager,
		router,
		store,
		render: h => h(App)
	})
} else if (beaverPopupsShortcodeGenerator) {
    new Vue({
        el: beaverPopupsShortcodeGenerator,
        store,
        render: h => h(PopupShortcodeGenerator)
    })
}
