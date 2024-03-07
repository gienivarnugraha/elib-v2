
import 'uno.css'
// import 'virtual:unocss-devtools'
// anu styles
import 'anu-vue/dist/style.css'
// default theme styles
import '@anu-vue/preset-theme-default/dist/style.css'
import '../css/app.css'

import { createApp } from 'vue';
import { anu } from 'anu-vue'
import mitt from 'mitt'

import App from '@/App.vue'
import HTTP from '@/services/HTTP'
import router from '@/router'
import store from '@/store'


export default class Application {
    constructor(config) {
        this.bus = mitt()
        this.config = config
        this.bootingCallbacks = []
        this.axios = HTTP
        this.booted = ref(false)
        this.axios.defaults.baseURL = this.config.apiURL
    }

    /**
     * Start the application
     *
     * @return {Void}
     */
    start() {
        let self = this

        const app = createApp(App)

        // app.use(i18n.instance)
        app.use(router)

        app.use(store)

        app.use(anu, {
            initialTheme: 'dark',
            // registerComponents: false
        });

        app.config.performance = import.meta.env.DEV

        this.boot(app, router)

        this.app = app

        app.mount('#app')

        this.booted.value = true

    }

    /**
     * Check if the user is logged in.
     *
     * @return {boolean} Returns true if the user is logged in, otherwise false.
     */
    isLoggedIn() {
        return !!this.config.is_logged_in && !!this.config.user
    }

    /**
     * Get the application CSRF token
     *
     * @return {String|null}
     */
    csrfToken() {
        return this.config.csrfToken || null
    }

    /**
     * Register a callback to be called before the application starts
     */
    booting(callback) {
        this.bootingCallbacks.push(callback)
    }

    /**
     * Execute all of the booting callbacks.
     */
    boot(app, router) {
        this.bootingCallbacks.forEach(callback => callback(app, router))
        this.bootingCallbacks = []
    }

    /**
     * Helper request function
     * @param  {Object} options
     * @return {Object}
     */
    request(options) {
        if (options !== undefined) {
            return this.axios(options)
        }

        return this.axios
    }

    /**
     * Register global event
     * @param  {mixed} args
     * @return {Void}
     */
    $on(...args) {
        this.bus.on(...args)
    }

    /**
     * Deregister event
     * @param  {mixed} args
     * @return {Void}
     */
    $off(...args) {
        this.bus.off(...args)
    }

    /**
     * Emit global event
     * @param  {mixed} args
     * @return {Void}
     */
    $emit(...args) {
        this.bus.emit(...args)
    }


    /**
     * A function that notifies the user with a message.
     *
     * @param {string} message - The message to be displayed.
     * @param {string} type - The type of notification.
     * @param {number} duration - The duration of the notification.
     * @return {undefined} This function does not return a value.
     */
    notify(message, type, options, duration) {
        this.app.config.globalProperties.$notify(
            Object.assign({}, options, {
                text: message,
                type: type,
                group: 'app',
            }),
            duration
        )
    }

    /**
     * Show toasted success messages
     *
     * @param {String} message
     * @param {Object} options
     * @param {Number} duration
     *
     * @return {Void}
     */
    success(message, options, duration = 4000) {
        this.notify(message, 'success', options, duration)
    }

    /**
     * Show toasted info messages
     *
     * @param {String} message
     * @param {Object} options
     * @param {Number} duration
     *
     * @return {Void}
     */
    info(message, options, duration = 4000) {
        this.notify(message, 'info', options, duration)
    }

    /**
     * Show toasted error messages
     *
     * @param {String} message
     * @param {Object} options
     * @param {Number} duration
     *
     * @return {Void}
     */
    error(message, options, duration = 4000) {
        this.notify(message, 'error', options, duration)
    }
}