<template>
  <q-page padding class="q-pa-xl">
    <div class="text-h4 text-weight-bold text-grey-9 q-mb-xs">Realizar Denúncia</div>
    <div class="text-subtitle1 text-grey-6 q-mb-lg">
      Use este formulário para relatar qualquer situação que julgar necessária.
    </div>

    <q-card flat bordered class="q-pa-lg" style="max-width: 640px;">
      <q-form @submit.prevent="handleSubmit" class="q-gutter-md">
        <q-input
          outlined type="textarea"
          v-model="descricao"
          rows="6"
          placeholder="Descreva aqui..."
        />

        <q-btn
          unelevated color="primary" label="Enviar Denúncia"
          type="submit" :loading="enviando"
          :disable="!descricao.trim()"
        />
      </q-form>
    </q-card>
  </q-page>
</template>

<script>
import { defineComponent, ref } from 'vue'
import useDenunciasApi from '../composables/UseDenunciasApi'
import useNotify from '../composables/UseNotify'

export default defineComponent({
  name: 'DenunciaPage',
  setup() {
    const { criarDenuncia } = useDenunciasApi()
    const { notifySuccess, notifyError } = useNotify()

    const descricao = ref('')
    const enviando = ref(false)

    const handleSubmit = async () => {
      try {
        enviando.value = true
        await criarDenuncia({ descricao: descricao.value })
        notifySuccess('Denúncia enviada.')
        descricao.value = ''
      } catch (error) {
        notifyError(error.message)
      } finally {
        enviando.value = false
      }
    }

    return { descricao, enviando, handleSubmit }
  },
})
</script>
