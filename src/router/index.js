import { defineRouter } from '#q-app'
import {
  createMemoryHistory,
  createRouter,
  createWebHashHistory,
  createWebHistory,
} from 'vue-router'
import { useSupabase } from '../boot/supabase'

import routes from './routes.js'

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default defineRouter((/* { store, ssrContext } */) => {
  const createHistory = import.meta.env.QUASAR_SERVER
    ? createMemoryHistory
    : import.meta.env.QUASAR_VUE_ROUTER_MODE === 'history'
      ? createWebHistory
      : createWebHashHistory

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(import.meta.env.QUASAR_VUE_ROUTER_BASE),
  })

  Router.beforeEach(async (to, from, next) => {
    const { supabase } = useSupabase()
    const { data: { session } } = await supabase.auth.getSession()
    const isAuthenticated = !!session

    if (to.meta.requiresAuth && !isAuthenticated) {
      // Rota protegida sem sessão → vai pro login
      next({ name: 'login' })
    } else if (to.name === 'login' && isAuthenticated) {
      // Já autenticado tentando acessar login → vai pra home
      next({ name: 'home' })
    } else {
      next()
    }
  })

  return Router
})