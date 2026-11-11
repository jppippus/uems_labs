<template>
  <q-page padding class="q-pa-xl">
    <div class="text-h4 text-weight-bold text-grey-9 q-mb-xs">Relatar Problema</div>
    <div class="text-subtitle1 text-grey-6 q-mb-lg">
      Reporte um problema num computador, mesa ou sala. O recurso é marcado como
      indisponível automaticamente até o técnico resolver.
    </div>

    <q-card flat bordered class="q-pa-lg" style="max-width: 640px;">
      <q-form @submit.prevent="handleSubmit" class="q-gutter-md">
        <div>
          <div class="text-body2 q-mb-xs">Laboratório</div>
          <q-select
            outlined dense
            v-model="laboratorioSelecionado"
            :options="laboratorios"
            option-label="nome"
            option-value="id"
            :loading="loading"
            @update:model-value="recursoSelecionado = null"
          />
        </div>

        <div v-if="laboratorioSelecionado">
          <div class="text-body2 q-mb-xs">O que você estava usando?</div>
          <q-select
            outlined dense
            v-model="recursoSelecionado"
            :options="recursosDoLaboratorio"
            :option-label="rotuloRecurso"
            option-value="id"
          />
        </div>

        <div v-if="recursoSelecionado">
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
          :disable="!recursoSelecionado || !tipoProblema"
        />
      </q-form>
    </q-card>
  </q-page>
</template>

<script>
// PENDENCIA (RF005): o requisito pede que o problema seja relatado "a partir
// de uma reserva realizada" (reserva_id obrigatorio, recurso_id derivado
// dela). Por enquanto deixamos o usuario escolher laboratorio/recurso
// direto, pra nao depender do endpoint de "Minhas Reservas" (Integrante B,
// api/reservas/listar.php) que ainda nao existe. Quando esse endpoint
// estiver pronto: trocar o seletor de laboratorio/recurso abaixo por um
// seletor das reservas do proprio usuario, e mandar reserva_id pro backend
// (o PHP passa a derivar o recurso_id a partir da reserva, em vez de
// confiar no recurso_id que o cliente manda).
import { defineComponent, ref, computed, onMounted } from 'vue'
import useProblemasApi from '../composables/UseProblemasApi'
import useNotify from '../composables/UseNotify'

const TIPOS_POR_RECURSO = {
  computador: ['Teclado', 'Monitor', 'Mouse', 'Não liga', 'Travando/lento', 'Outro'],
  sala: ['Ar condicionado', 'Iluminação', 'Limpeza', 'Outro'],
  mesa: ['Quebrada/danificada', 'Suja', 'Outro'],
}

export default defineComponent({
  name: 'RelatarProblemaPage',
  setup() {
    const { getLaboratorios, relatarProblema } = useProblemasApi()
    const { notifySuccess, notifyError } = useNotify()

    const laboratorios = ref([])
    const loading = ref(false)
    const enviando = ref(false)

    const laboratorioSelecionado = ref(null)
    const recursoSelecionado = ref(null)
    const tipoProblema = ref(null)
    const descricao = ref('')

    const recursosDoLaboratorio = computed(
      () => laboratorioSelecionado.value?.recursos ?? [],
    )

    const opcoesDeTipo = computed(() => {
      const tipo = recursoSelecionado.value?.tipo
      return TIPOS_POR_RECURSO[tipo] ?? []
    })

    const rotuloRecurso = (recurso) => `${recurso.identificacao} (${recurso.tipo})`

    onMounted(async () => {
      try {
        loading.value = true
        laboratorios.value = await getLaboratorios()
      } catch (error) {
        notifyError(error.message)
      } finally {
        loading.value = false
      }
    })

    const handleSubmit = async () => {
      try {
        enviando.value = true
        await relatarProblema({
          recurso_id: recursoSelecionado.value.id,
          tipo_problema: tipoProblema.value,
          descricao: descricao.value || null,
        })
        notifySuccess('Problema relatado. O recurso foi marcado como indisponível.')
        recursoSelecionado.value = null
        tipoProblema.value = null
        descricao.value = ''
      } catch (error) {
        notifyError(error.message)
      } finally {
        enviando.value = false
      }
    }

    return {
      laboratorios,
      loading,
      enviando,
      laboratorioSelecionado,
      recursoSelecionado,
      tipoProblema,
      descricao,
      recursosDoLaboratorio,
      opcoesDeTipo,
      rotuloRecurso,
      handleSubmit,
    }
  },
})
</script>
