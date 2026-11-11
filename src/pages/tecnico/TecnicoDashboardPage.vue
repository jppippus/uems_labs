<template>
  <q-page padding>
    <div class="q-mb-md">
      <h4 class="text-weight-bold q-my-none">Painel do Técnico</h4>
      <p class="text-grey-7">Reservas ativas programadas para hoje</p>
    </div>

    <q-card flat bordered class="q-pa-md">
      <q-table
        title="Reservas de Hoje"
        :rows="reservas"
        :columns="columns"
        row-key="id"
        :loading="loading"
      >
        <template #body-cell-status="props">
          <q-td :props="props">
            <q-chip
              dense
              :color="props.row.status === 'ativa' ? 'green' : 'orange'"
              text-color="white"
            >
              {{ props.row.status }}
            </q-chip>
          </q-td>
        </template>
      </q-table>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import useTecnicoApi from '../../composables/UseTecnicoApi'
import useNotify from '../../composables/UseNotify'

const { getDashboard } = useTecnicoApi()
const { notifyError } = useNotify()

const reservas = ref([])
const loading = ref(false)

const columns = [
  { name: 'laboratorio_nome', label: 'Laboratório', field: 'laboratorio_nome', align: 'left', sortable: true },
  { name: 'usuario_nome', label: 'Usuário', field: 'usuario_nome', align: 'left', sortable: true },
  { name: 'hora_inicio', label: 'Início', field: 'hora_inicio', align: 'left', sortable: true },
  { name: 'hora_fim', label: 'Fim', field: 'hora_fim', align: 'left', sortable: true },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
]

const carregarReservas = async () => {
  loading.value = true
  try {
    reservas.value = await getDashboard()
  } catch (error) {
    notifyError('Erro ao carregar as reservas do dashboard.', error.message)
  } finally {
    loading.value = false
  }
}

onMounted(carregarReservas)
</script>
