<template>
      <q-page class="flex flex-center bg-primary text-white q-pa-md">
        <div class="column items-center q-mb-xl">
          <div class="text-h4 text-weight-bold q-mb-sm text-center">
            Sistema de Reserva de<br>Laboratórios da UEMS
          </div>
          <div class="text-subtitle1 text-weight-light">
            Gerencie suas reservas de laboratório
          </div>

          <q-card class="q-mt-lg q-pa-lg text-black shadow-4" style="width: 100%; max-width: 400px; border-radius: 8px;">
            <q-card-section class="column items-center q-pb-none">
              <q-img src="../assets/logo-uems.png" style="width: 180px;" class="q-mb-md" />
            </q-card-section>

            <q-card-section>
              <q-form @submit.prevent="handleLogin" class="q-gutter-md">
                <div>
                  <div class="text-body2 q-mb-xs">Email Acadêmico</div>
                  <q-input outlined dense v-model="form.email" type="email" />
                </div>

                <div>
                  <div class="text-body2 q-mb-xs">Senha</div>
                  <q-input outlined dense v-model="form.password" type="password" />
                </div>

                <q-btn 
                  unelevated 
                  color="positive" 
                  label="Entrar" 
                  class="full-width q-mt-sm" 
                  type="submit" 
                  :loading="loading"
                />
              </q-form>

              <div class="q-mt-md">
                <a href="#" class="text-primary text-caption" style="text-decoration: underline;">
                  Esqueceu a senha?
                </a>
              </div>
            </q-card-section>

            <q-card-section class="q-pt-none q-mt-md">
              <q-card class="bg-grey-1 shadow-0 border-grey" bordered>
                <q-card-section class="text-caption text-grey-7 q-pa-sm">
                  Universidade Estadual de Mato Grosso do Sul (UEMS)<br>
                  © 2026 - Sistema de Reserva de Laboratórios
                </q-card-section>
              </q-card>
            </q-card-section>
          </q-card>
        </div>
      </q-page>
</template>

<script>
import { defineComponent, ref } from 'vue'
import { useRouter } from 'vue-router'
import useAuthUser from '../composables/UseAuthUser'
import useNotify from '../composables/UseNotify'

export default defineComponent({
  name: 'LoginPage',
  setup() {
    const router = useRouter()
    const { login } = useAuthUser()
    const { notifyError, notifySuccess } = useNotify()
    
    const form = ref({ email: '', password: '' })
    const loading = ref(false)

    const handleLogin = async () => {
      try {
        loading.value = true
        await login(form.value) // Mock no Supabase
        notifySuccess('Login realizado com sucesso!')
        router.push({ name: 'home' })
      } catch (error) {
        notifyError('Credenciais inválidas.', error.message)
      } finally {
        loading.value = false
      }
    }

    return { form, loading, handleLogin }
  }
})
</script>