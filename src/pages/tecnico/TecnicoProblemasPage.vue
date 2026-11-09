<template>
  <q-page padding>
    <div class="q-mb-md">
      <h4 class="text-weight-bold q-my-none">Gestão de Problemas</h4>
      <p class="text-grey-7">Problemas relatados pelos usuários nos laboratórios</p>
    </div>

    <q-card flat bordered class="q-pa-md">
      <q-table
        title="Problemas Relatados"
        :rows="problemas"
        :columns="columns"
        row-key="id"
        :loading="loading"
      >
        <template #body-cell-status="props">
          <q-td :props="props">
            <q-chip
              dense
              :color="props.row.status === 'aberto' ? 'orange' : 'green'"
              text-color="white"
            >
              {{ props.row.status }}
            </q-chip>
          </q-td>
        </template>

        <template #body-cell-acoes="props">
          <q-td :props="props">
            <q-btn
              dense
              color="primary"
              label="Resolver"
              icon="check"
              :disable="props.row.status === 'resolvido'"
              :loading="resolvendoId === props.row.id"
              @click="resolver(props.row.id)"
            />
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

const { getProblemas, resolverProblema } = useTecnicoApi()
const { notifySuccess, notifyError } = useNotify()

const problemas = ref([])
const loading = ref(false)
const resolvendoId = ref(null)

const columns = [
  { name: 'laboratorio_nome', label: 'Laboratório', field: 'laboratorio_nome', align: 'left', sortable: true },
  { name: 'recurso_identificacao', label: 'Recurso', field: 'recurso_identificacao', align: 'left', sortable: true },
  { name: 'tipo_problema', label: 'Problema', field: 'tipo_problema', align: 'left', sortable: true },
  { name: 'descricao', label: 'Descrição', field: 'descricao', align: 'left' },
  { name: 'status', label: 'Status', field: 'status', align: 'center', sortable: true },
  { name: 'acoes', label: 'Ações', field: 'acoes', align: 'center' },
]

const carregarProblemas = async () => {
  loading.value = true
  try {
    problemas.value = await getProblemas()
  } catch (error) {
    notifyError('Erro ao carregar os problemas relatados.', error.message)
  } finally {
    loading.value = false
  }
}

const resolver = async (id) => {
  try {
    resolvendoId.value = id
    await resolverProblema(id)
    notifySuccess('Problema resolvido!')
    await carregarProblemas()
  } catch (error) {
    notifyError('Erro ao tentar resolver o problema.', error.message)
  } finally {
    resolvendoId.value = null
  }
}

onMounted(carregarProblemas)
</script>
