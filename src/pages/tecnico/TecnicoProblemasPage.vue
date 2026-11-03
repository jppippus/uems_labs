<template>
  <q-page padding>
    <div class="q-mb-md">
      <h4 class="text-weight-bold q-my-none">Gestão de Problemas</h4>
      <p class="text-grey-7">Lista de problemas relatados pelos utilizadores nos laboratórios</p>
    </div>

    <q-card flat bordered class="q-pa-md">
      <q-table
        title="Problemas Relatados"
        :rows="problemas"
        :columns="columns"
        row-key="id"
        :loading="loading"
      >
        <template #body-cell-acoes="props">
          <q-td :props="props">
            <q-btn
              dense
              color="primary"
              label="Resolver"
              icon="check"
              :disabled="props.row.status === 'resolvido'"
              @click="resolverProblema(props.row.id)"
            />
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
const problemas = ref([])
const loading = ref(false)

const columns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left', sortable: true },
  {
    name: 'descricao',
    label: 'Descrição do Problema',
    field: 'descricao',
    align: 'left',
    sortable: true,
  },
  { name: 'status', label: 'Status', field: 'status', align: 'center', sortable: true },
  { name: 'acoes', label: 'Ações', field: 'acoes', align: 'center' },
]

const carregarProblemas = async () => {
  loading.value = true
  try {
    const response = await api.get('/problemas/listar.php')
    if (response.data.ok) {
      problemas.value = response.data.problemas
    }
  } catch {
    $q.notify({
      color: 'negative',
      message: 'Erro ao carregar os problemas relatados.',
      icon: 'report_problem',
    })
  } finally {
    loading.value = false
  }
}

const resolverProblema = async (id) => {
  try {
    const response = await api.post('/problemas/resolver.php', { problema_id: id })
    if (response.data.ok) {
      $q.notify({
        color: 'positive',
        message: 'Problema resolvido!',
        icon: 'check_circle',
      })
      carregarProblemas()
    }
  } catch {
    $q.notify({
      color: 'negative',
      message: 'Erro ao tentar resolver o problema.',
      icon: 'report_problem',
    })
  }
}

onMounted(() => {
  carregarProblemas()
})
</script>
