<template>
  <q-page padding class="q-pa-md q-pa-md-xl">
    <div class="text-h4 text-weight-bold text-grey-9 q-mb-xs">Minhas Reservas</div>
    <div class="text-subtitle1 text-grey-6 q-mb-lg">
      Acompanhe suas reservas ativas e o histórico das anteriores.
    </div>

    <q-tabs v-model="aba" align="left" class="text-grey-8" active-color="primary" indicator-color="primary">
      <q-tab name="ativas" :label="`Ativas (${ativas.length})`" />
      <q-tab name="historico" :label="`Histórico (${historico.length})`" />
    </q-tabs>
    <q-separator class="q-mb-md" />

    <div v-if="loading" class="row justify-center q-pa-xl">
      <q-spinner color="primary" size="40px" />
    </div>

    <q-tab-panels v-else v-model="aba" animated class="bg-transparent">
      <q-tab-panel name="ativas" class="q-pa-none">
        <div v-if="ativas.length === 0" class="text-center text-grey-6 q-pa-xl">
          <q-icon name="mdi-calendar-blank-outline" size="48px" />
          <div class="q-mt-sm">Você não possui reservas ativas.</div>
        </div>
        <div v-else class="column q-gutter-y-md">
          <q-card v-for="r in ativas" :key="r.id" flat bordered>
            <q-card-section class="row items-center no-wrap">
              <div class="col">
                <div class="text-subtitle1 text-weight-bold">{{ r.laboratorio_nome }}</div>
                <div class="text-body2 text-grey-8">
                  {{ formatarData(r.data_reserva) }} • {{ formatarHora(r.hora_inicio) }} às {{ formatarHora(r.hora_fim) }}
                </div>
                <div class="text-caption text-grey-6">
                  {{ r.recurso_identificacao }} ({{ r.recurso_tipo }}) • {{ r.tipo }}
                  <span v-if="r.proposito"> • {{ r.proposito }}</span>
                </div>
              </div>
              <q-btn outline color="negative" label="Cancelar" icon="mdi-close" @click="abrirConfirmacao(r)" />
            </q-card-section>
          </q-card>
        </div>
      </q-tab-panel>

      <q-tab-panel name="historico" class="q-pa-none">
        <div v-if="historico.length === 0" class="text-center text-grey-6 q-pa-xl">
          <q-icon name="mdi-history" size="48px" />
          <div class="q-mt-sm">Nenhuma reserva anterior.</div>
        </div>
        <div v-else class="column q-gutter-y-md">
          <q-card v-for="r in historico" :key="r.id" flat bordered>
            <q-card-section class="row items-center no-wrap">
              <div class="col">
                <div class="text-subtitle1 text-weight-bold">{{ r.laboratorio_nome }}</div>
                <div class="text-body2 text-grey-8">
                  {{ formatarData(r.data_reserva) }} • {{ formatarHora(r.hora_inicio) }} às {{ formatarHora(r.hora_fim) }}
                </div>
                <div class="text-caption text-grey-6">
                  {{ r.recurso_identificacao }} ({{ r.recurso_tipo }}) • {{ r.tipo }}
                  <span v-if="r.proposito"> • {{ r.proposito }}</span>
                </div>
              </div>
              <q-chip dense text-color="white" :color="r.status === 'cancelada' ? 'grey-6' : 'green'">
                {{ r.status === 'cancelada' ? 'Cancelada' : 'Concluída' }}
              </q-chip>
            </q-card-section>
          </q-card>
        </div>
      </q-tab-panel>
    </q-tab-panels>

    <q-dialog v-model="confirmando">
      <q-card style="min-width: 320px">
        <q-card-section>
          <div class="text-h6">Cancelar reserva?</div>
          <div v-if="reservaSelecionada" class="text-body2 text-grey-8 q-mt-sm">
            {{ reservaSelecionada.laboratorio_nome }} —
            {{ formatarData(reservaSelecionada.data_reserva) }},
            {{ formatarHora(reservaSelecionada.hora_inicio) }} às {{ formatarHora(reservaSelecionada.hora_fim) }}
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Voltar" v-close-popup :disable="cancelando" />
          <q-btn unelevated color="negative" label="Cancelar reserva" :loading="cancelando" @click="confirmarCancelamento" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import useApi from '../composables/UseApi'
import useNotify from '../composables/UseNotify'

defineOptions({ name: 'MinhasReservasPage' })

const { apiGet, apiPost } = useApi()
const { notifySuccess, notifyError } = useNotify()

const aba = ref('ativas')
const loading = ref(false)
const ativas = ref([])
const historico = ref([])

const confirmando = ref(false)
const cancelando = ref(false)
const reservaSelecionada = ref(null)

const formatarData = (iso) => {
  const [ano, mes, dia] = String(iso).split('-')
  return `${dia}/${mes}/${ano}`
}
const formatarHora = (hora) => String(hora).slice(0, 5)

const carregar = async () => {
  loading.value = true
  try {
    const dados = await apiGet('/reservas/listar.php')
    ativas.value = dados.ativas
    historico.value = dados.historico
  } catch (error) {
    notifyError('Erro ao carregar suas reservas.', error.message)
  } finally {
    loading.value = false
  }
}

const abrirConfirmacao = (reserva) => {
  reservaSelecionada.value = reserva
  confirmando.value = true
}

const confirmarCancelamento = async () => {
  cancelando.value = true
  try {
    await apiPost('/reservas/cancelar.php', { reserva_id: reservaSelecionada.value.id })
    notifySuccess('Reserva cancelada.')
    confirmando.value = false
    await carregar()
  } catch (error) {
    notifyError('Erro ao cancelar a reserva.', error.message)
  } finally {
    cancelando.value = false
  }
}

onMounted(carregar)
</script>
