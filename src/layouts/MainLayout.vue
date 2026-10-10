<template>
  <q-layout view="lHh Lpr lFf" class="bg-grey-1">
    <q-drawer show-if-above class="bg-grey-10 text-white" :width="280">
      <div class="q-pa-md">
        <div class="row items-center q-mb-xl">
          <q-avatar color="primary" text-color="white" size="40px" class="q-mr-sm font-weight-bold">
            U
          </q-avatar>
          <div>
            <div class="text-weight-bold">UEMS Labs</div>
            <div class="text-caption text-grey-5">Reserva de laboratórios</div>
          </div>
        </div>

        <q-card class="bg-grey-9 text-white q-mb-md shadow-0" bordered style="border-color: #424242;">
          <q-card-section class="row items-center q-pa-sm">
            <q-avatar color="grey-7" text-color="white" size="32px" class="q-mr-sm">
              A
            </q-avatar>
            <div class="text-weight-medium">Acadêmico</div>
          </q-card-section>
        </q-card>

        <q-list class="q-gutter-y-xs">
          <q-item clickable v-ripple active-class="bg-grey-9 text-white" to="/" exact>
            <q-item-section avatar><q-icon name="mdi-home-outline" /></q-item-section>
            <q-item-section>Início</q-item-section>
          </q-item>
          <q-item clickable v-ripple to="/reservas">
            <q-item-section avatar><q-icon name="mdi-calendar-check-outline" /></q-item-section>
            <q-item-section>Minhas Reservas</q-item-section>
          </q-item>
          <q-item clickable v-ripple to="/problemas">
            <q-item-section avatar><q-icon name="mdi-alert-outline" /></q-item-section>
            <q-item-section>Relatar Problema</q-item-section>
          </q-item>
          <q-item clickable v-ripple to="/denuncia">
            <q-item-section avatar><q-icon name="mdi-flag-outline" /></q-item-section>
            <q-item-section>Realizar Denúncia</q-item-section>
          </q-item>
        </q-list>
      </div>

      <div class="absolute-bottom q-pa-md">
        <q-item clickable v-ripple @click="handleLogout">
          <q-item-section avatar><q-icon name="mdi-logout" /></q-item-section>
          <q-item-section>Sair</q-item-section>
        </q-item>
      </div>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent } from 'vue'
import { useRouter } from 'vue-router'
import useAuthUser from '../composables/UseAuthUser'

export default defineComponent({
  name: 'MainLayout',
  setup() {
    const router = useRouter()
    const { logout } = useAuthUser()

    const handleLogout = async () => {
      await logout()
      router.push({ name: 'login' })
    }

    return { handleLogout }
  }
})
</script>