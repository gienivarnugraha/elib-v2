import Init from '@/init'
import Gate from '@/gate'

import { useConfigStore } from '@/store/config'
import registerComponents from '@/components'
import registerDirectives from '@/directives'

const app = new Init(config)


app.booting((Vue, router) => {
    const configStore = useConfigStore()
    Object.keys(config).forEach(key => configStore.$state[key] = config[key])

    // Vue.config.globalProperties.$gate = new Gate(config.user)

    console.log(config);

    // console.log(Vue.config.globalProperties.$gate.isSuperAdmin());
})

app.booting((Vue, router) => {
    router.beforeEach((to, from, next) => {
        const requiresAuth = to.meta.requiresAuth

        if (requiresAuth && !app.isLoggedIn()) {
            return next({ path: 'login' })
        }

        if (to.name === 'login' && app.isLoggedIn()) {
            return next({ path: from.path })
        }

        const gateRoute = to.matched.find(match => match.meta.gate)

        if (gateRoute && typeof gateRoute.meta.gate === 'string') {
            if (Vue.config.globalProperties.$gate.userCant(gateRoute.meta.gate)) {
                return next({ path: '/403' })
            }
        }
        return next()
    })
})



app.booting((Vue, router) => {
    registerComponents(Vue)
    registerDirectives(Vue)
})

window.Application = app
window.Gate = new Gate(config.user)
Application.start()