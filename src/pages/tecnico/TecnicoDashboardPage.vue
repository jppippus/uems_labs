<template>
  <q-page padding>
    <div class="q-mb-md">
      <h4 class="text-weight-bold q-my-none">Painel do Técnico</h4>
      <p class="text-grey-7">Acompanhamento das reservas e laboratórios do sistema</p>
    </div>

    <q-card flat bordered class="q-pa-md">
      <q-table
        title="Reservas Registadas"
        :rows="reservas"
        :columns="columns"
        row-key="id"
        :loading="loading"
      >
        <template #body-cell-status="props">
          <q-td :props="props">
            <q-chip
              dense
              :color="props.row.status === 'ativo' ? 'green' : 'orange'"
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
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const reservas = ref([])
const loading = ref(false)

const columns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left', sortable: true },
  {
    name: 'laboratorio_nome',
    label: 'Laboratório',
    field: 'laboratorio_nome',
    align: 'left',
    sortable: true,
  },
  {
    name: 'usuario_nome',
    label: 'Utilizador',
    field: 'usuario_nome',
    align: 'left',
    sortable: true,
  },
  { name: 'data', label: 'Data', field: 'data', align: 'left', sortable: true },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
]

const carregarReservas = async () => {
  loading.value = true
  try {
    const response = await api.get('/tecnico/dashboard.php')
    if (response.data.ok) {
      reservas.value = response.data.reservas
    }
  } catch {
    $q.notify({
      color: 'negative',
      message: 'Erro ao carregar as reservas do dashboard.',
      icon: 'report_problem',
    })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  carregarReservas()
})
</script>
