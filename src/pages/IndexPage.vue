<template>
  <q-page padding class="q-pa-md q-pa-md-xl">
    <div class="row justify-between items-center q-mb-lg">
      <div>
        <div class="text-h4 text-weight-bold text-grey-9">Bem-vindo, {{ nomeUsuario }}</div>
        <div class="text-subtitle1 text-grey-6">{{ dataAtual }}</div>
      </div>
      <q-btn flat round icon="mdi-bell-outline" color="grey-8" class="bg-white shadow-1" />
    </div>

    <!-- Ajuste com items-stretch para garantir mesma altura e padding do grid -->
    <div class="row q-col-gutter-md q-mb-xl items-stretch">
      <div class="col-12 col-md-6">
        <q-card class="shadow-1 q-pa-md full-height column justify-between" style="border-radius: 12px;">
          <q-card-section>
            <div class="row justify-between items-center">
              <div class="text-subtitle2 text-grey-7 text-weight-bold">Reservas Ativas</div>
              <q-icon name="mdi-calendar-range" size="sm" color="primary" class="bg-blue-1 q-pa-xs rounded-borders" />
            </div>
            
            <div v-if="qtdReservas > 0">
              <div class="text-h3 text-weight-bold q-mt-sm">{{ qtdReservas }}</div>
              <div class="text-caption text-grey-6 q-mt-sm">reservas agendadas para esta semana</div>
            </div>
            
            <div v-else class="q-mt-md text-center bg-grey-2 q-pa-sm rounded-borders">
              <q-icon name="mdi-calendar-blank-outline" size="md" color="grey-5" />
              <div class="text-weight-bold text-grey-7 q-mt-xs">Nenhuma reserva ativa</div>
              <div class="text-caption text-grey-6">Você não possui agendamentos para esta semana.</div>
            </div>
            
          </q-card-section>
        </q-card>
      </div>
      
      <div class="col-12 col-md-6">
        <q-card class="shadow-1 q-pa-md full-height column justify-between" style="border-radius: 12px;">
          <q-card-section>
            <div class="row justify-between items-center">
              <div class="text-subtitle2 text-grey-7 text-weight-bold">Próxima Reserva</div>
              <q-icon name="mdi-clock-outline" size="sm" color="primary" class="bg-blue-1 q-pa-xs rounded-borders" />
            </div>
            
            <div v-if="proximaReserva">
              <div class="text-h5 text-weight-bold q-mt-md">{{ proximaReserva.laboratorio }} - {{ proximaReserva.dataHora }}</div>
              <div class="text-caption text-grey-6 q-mt-xs">{{ proximaReserva.localizacao }} • {{ proximaReserva.recursos }}</div>
            </div>
            
            <div v-else class="q-mt-md text-center bg-grey-2 q-pa-sm rounded-borders">
               <q-icon name="mdi-clock-alert-outline" size="md" color="grey-5" />
               <div class="text-weight-bold text-grey-7 q-mt-xs">Sem próximos agendamentos</div>
               <div class="text-caption text-grey-6">Faça uma nova reserva abaixo.</div>
            </div>
            
          </q-card-section>
        </q-card>
      </div>
    </div>

    <div class="row justify-between items-center q-mb-md">
      <div class="text-h6 text-weight-bold">Laboratórios Disponíveis</div>
      <a href="#" class="text-grey-8" style="text-decoration: underline;">Ver todos</a>
    </div>

    <div class="row q-col-gutter-md">
      <!-- Exibe mensagem se estiver carregando ou não houver laboratórios -->
      <div v-if="carregando" class="col-12 text-center q-pa-md text-grey-6">Carregando laboratórios...</div>
      <div v-else-if="laboratorios.length === 0" class="col-12 text-center q-pa-md text-grey-6">Nenhum laboratório encontrado na base de dados.</div>

      <!-- Loop nos Laboratórios Reais -->
      <div class="col-12 col-md-6" v-for="lab in laboratorios" :key="lab.id">
        <q-card class="shadow-1" style="border-radius: 12px;">
          <q-card-section>
            <div class="row justify-between items-center q-mb-sm">
              <div class="text-h6 text-weight-bold">{{ lab.nome }}</div>
              <!-- Mantém tudo verde/disponível por enquanto, até ter a rota de status -->
              <q-badge color="positive" rounded transparent class="q-px-sm q-py-xs">
                <q-icon name="mdi-circle" size="8px" class="q-mr-xs" /> Disponível
              </q-badge>
            </div>
            <div class="text-body2 text-grey-7 row items-center q-mb-md">
              <q-icon name="mdi-map-marker-outline" size="xs" class="q-mr-xs" />
              {{ lab.localizacao }}
            </div>
            <q-separator />
            <div class="row justify-between items-center q-mt-md">
              <div class="text-caption text-grey-6">Horário de Funcionamento</div>
              <q-btn 
                unelevated 
                color="primary" 
                label="Reservar" 
                class="q-px-md" 
                style="border-radius: 6px;"
                :to="`/reservar/${lab.id}`" 
              />
              <!-- ^ AQUI ESTÁ O SEGREDO: O botão agora envia o UUID real! -->
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import useReservasApi from '../composables/UseReservaApi'
import useApi from '../composables/UseApi'
import useAuthUser from '../composables/UseAuthUser'

const { listarLaboratorios } = useReservasApi()
const { apiGet } = useApi()
const { nome, user } = useAuthUser()

defineOptions({ name: 'IndexPage' })

const nomeUsuario = computed(() => (nome.value || user.value?.email || 'Acadêmico').split(' ')[0])
const dataAtual = ref('')
const laboratorios = ref([])
const carregando = ref(true)

const minhasReservasAtivas = ref([])

const qtdReservas = computed(() => minhasReservasAtivas.value.length)

const proximaReserva = computed(() => {
  const r = minhasReservasAtivas.value[0]
  if (!r) return null

  const [ano, mes, dia] = r.data_reserva.split('-')
  const hoje = new Date().toISOString().slice(0, 10)
  const dataFormatada = r.data_reserva === hoje ? 'Hoje' : `${dia}/${mes}/${ano}`

  return {
    laboratorio: r.laboratorio_nome,
    dataHora: `${dataFormatada}, ${r.hora_inicio.slice(0, 5)}`,
    localizacao: r.laboratorio_localizacao,
    recursos: `${r.recurso_identificacao} (${r.recurso_tipo})`,
  }
})

const formatarData = () => {
  const opcoes = { day: 'numeric', month: 'long', year: 'numeric' }
  const data = new Date()
  dataAtual.value = `Hoje, ${data.toLocaleDateString('pt-BR', opcoes)}`
}

const carregarDadosIniciais = async () => {
  try {
    const [dadosLabs, minhasReservas] = await Promise.all([
      listarLaboratorios(),
      apiGet('/reservas/listar.php'),
    ])

    laboratorios.value = Array.isArray(dadosLabs) ? dadosLabs : (dadosLabs.data || dadosLabs.laboratorios || [])
    minhasReservasAtivas.value = minhasReservas.ativas || []
  } catch (error) {
    console.error('Erro ao buscar dados:', error)
  } finally {
    carregando.value = false
  }
}

onMounted(() => {
  formatarData()
  carregarDadosIniciais()
})
</script>