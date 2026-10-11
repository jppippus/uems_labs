const routes = [
  {
    path: '/',
    component: () => import('../layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [ 
      // Quando acessar '/', carrega a IndexPage
      { path: '', name: 'home', component: () => import('../pages/IndexPage.vue') },
      // Quando acessar '/reservas', carrega a ReservaPage (temporário até criarmos a de listagem)
      { path: 'reservas', name: 'reservas', component: () => import('../pages/ReservaPage.vue') },
      // RF005 - Relatar Problema
      { path: 'problemas', name: 'problemas', component: () => import('../pages/RelatarProblemaPage.vue') },
      // RF007 - Realizar Denúncia
      { path: 'denuncia', name: 'denuncia', component: () => import('../pages/DenunciaPage.vue') },
    ]
  },
  {
    path: '/auth',
    component: () => import('../layouts/LoginLayout.vue'),
    children: [
      { path: 'login', name: 'login', component: () => import('../pages/LoginPage.vue') },
    ],
  },
  {
    path: '/:catchAll(.*)*',
    component: () => import('../pages/ErrorNotFound.vue'),
  },
]

export default routes