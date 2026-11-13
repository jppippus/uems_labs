<template>
  <q-page padding class="q-pa-xl">
    <div class="text-h4 text-weight-bold text-grey-9 q-mb-xs">Relatar Problema</div>
    <div class="text-subtitle1 text-grey-6 q-mb-lg">
      Reporte um problema a partir de uma reserva sua. O recurso é marcado como
      indisponível automaticamente até o técnico resolver.
    </div>

    <q-card v-if="!loading && reservas.length === 0" flat bordered class="q-pa-lg text-center text-grey-6" style="max-width: 640px;">
      <q-icon name="mdi-calendar-remove-outline" size="48px" class="q-mb-sm" />
      <div>Você não tem reservas ativas no momento.</div>
      <div class="text-caption">Faça uma reserva antes de relatar um problema.</div>
    </q-card>

    <q-card v-else flat bordered class="q-pa-lg" style="max-width: 640px;">
      <q-form @submit.prevent="handleSubmit" class="q-gutter-md">
        <div>
          <div class="text-body2 q-mb-xs">Qual reserva?</div>
          <q-select
            outlined dense
            v-model="reservaSelecionada"
            :options="reservas"
            :option-label="rotuloReserva"
            :loading="loading"
          />
        </div>

        <div v-if="reservaSelecionada">
          <div class="text-body2 q-mb-xs">Tipo do problema</div>
          <q-select
            outlined dense
            v-model="tipoProblema"
            :options="opcoesDeTipo"
          />
        </div>

        <div>
          <div class="text-body2 q-mb-xs">Descrição (opcional)</div>
          <q-input
            outlined dense type="textarea"
            v-model="descricao"
            placeholder="Detalhe o problema, se quiser"
          />
        </div>

        <q-btn
          unelevated color="primary" label="Relatar Problema"
          type="submit" :loading="enviando"
          :disable="!reservaSelecionada || !tipoProblema"
        />
      </q-form>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import useProblemasApi from '../composables/UseProblemasApi'
import useNotify from '../composables/UseNotify'

const TIPOS_POR_RECURSO = {
  computador: ['Teclado', 'Monitor', 'Mouse', 'Não liga', 'Travando/lento', 'Outro'],
  sala: ['Ar condicionado', 'Iluminação', 'Limpeza', 'Outro'],
  mesa: ['Quebrada/danificada', 'Suja', 'Outro'],
}

const { getMinhasReservasAtivas, relatarProblema } = useProblemasApi()
const { notifySuccess, notifyError } = useNotify()

const reservas = ref([])
const loading = ref(false)
const enviando = ref(false)

const reservaSelecionada = ref(null)
const tipoProblema = ref(null)
const descricao = ref('')

const opcoesDeTipo = computed(() => TIPOS_POR_RECURSO[reservaSelecionada.value?.recurso_tipo] ?? [])

const rotuloReserva = (reserva) => {
  const [ano, mes, dia] = reserva.data_reserva.split('-')
  return `${reserva.laboratorio_nome} — ${reserva.recurso_identificacao} (${dia}/${mes}/${ano}, ${reserva.hora_inicio.slice(0, 5)})`
}

onMounted(async () => {
  try {
    loading.value = true
    reservas.value = await getMinhasReservasAtivas()
  } catch (error) {
    notifyError('Erro ao carregar suas reservas.', error.message)
  } finally {
    loading.value = false
  }
})

const handleSubmit = async () => {
  try {
    enviando.value = true
    await relatarProblema({
      reserva_id: reservaSelecionada.value.id,
      tipo_problema: tipoProblema.value,
      descricao: descricao.value || null,
    })
    notifySuccess('Problema relatado. O recurso foi marcado como indisponível.')
    reservaSelecionada.value = null
    tipoProblema.value = null
    descricao.value = ''
  } catch (error) {
    notifyError('Erro ao relatar o problema.', error.message)
  } finally {
    enviando.value = false
  }
}
</script>
