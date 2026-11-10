<template>
  <q-page padding class="q-pa-xl">
    <q-breadcrumbs class="text-grey-7 q-mb-md">
      <q-breadcrumbs-el label="Início" to="/" />
      <q-breadcrumbs-el label="Laboratórios" />
      <q-breadcrumbs-el :label="labAtual ? labAtual.nome : 'Carregando...'" />
      <q-breadcrumbs-el label="Reservar" class="text-primary text-weight-bold" />
    </q-breadcrumbs>

    <div class="row justify-between items-center q-mb-lg">
      <div>
        <div class="text-h4 text-weight-bold text-grey-9">
          Reservar {{ labAtual ? labAtual.nome : '...' }}
        </div>
        
        <div class="text-subtitle2 text-grey-6 row items-center q-mt-xs">
          <q-icon name="mdi-map-marker" size="sm" class="q-mr-xs" />
          {{ labAtual ? labAtual.localizacao : '...' }} 
          <span class="q-mx-sm">•</span> 
          {{ labAtual ? todosComputadores.length : '...' }} Computadores
          <span class="q-mx-sm">•</span> 
          {{ labAtual ? todasMesas.length : '...' }} Mesas
        </div>
      </div>
      
      <q-badge color="positive" rounded transparent class="q-px-md q-py-sm text-subtitle2">
        <q-icon name="mdi-circle" size="10px" class="q-mr-sm" /> Disponível para reservas
      </q-badge>
    </div>

    <!-- Seção: Tipo de Reserva e Recurso -->
    <div class="row q-col-gutter-lg q-mb-xl">
      <div class="col-12 col-md-5">
        <div class="text-subtitle2 text-weight-bold q-mb-sm text-grey-8">TIPO DE RESERVA</div>
        <!-- Ajuste de row q-col-gutter-sm e estilo dos cards para terem a mesma altura -->
        <div class="row q-col-gutter-sm items-stretch">
          <div class="col-6">
            <q-card 
              bordered 
              class="q-pa-sm full-height column justify-between" 
              :class="tipoReserva === 'normal' ? 'border-primary bg-blue-1' : 'border-grey cursor-pointer'" 
              @click="toggleTipoReserva('normal')"
            >
              <div>
                <div class="row justify-between items-center q-mb-xs">
                  <div class="text-weight-bold" :class="tipoReserva === 'normal' ? 'text-primary' : 'text-grey-8'">Reserva Normal</div>
                  <q-icon :name="tipoReserva === 'normal' ? 'mdi-check-circle' : 'mdi-circle-outline'" :color="tipoReserva === 'normal' ? 'primary' : 'grey-4'" size="sm" />
                </div>
                <div class="text-caption text-grey-6">Sessão única de uso imediato ou agendado para a semana.</div>
              </div>
            </q-card>
          </div>
          <div class="col-6">
            <q-card 
              bordered 
              class="q-pa-sm full-height column justify-between" 
              :class="isProfessor ? (tipoReserva === 'fixa' ? 'border-primary bg-blue-1 cursor-pointer' : 'border-grey cursor-pointer') : 'bg-grey-2 border-grey'" 
              @click="toggleTipoReserva('fixa')"
            >
              <div>
                <div class="row justify-between items-center q-mb-xs">
                  <div class="text-weight-bold" :class="isProfessor ? (tipoReserva === 'fixa' ? 'text-primary' : 'text-grey-8') : 'text-grey-5'">
                    Reserva Fixa
                  </div>
                  <q-icon 
                    :name="tipoReserva === 'fixa' ? 'mdi-check-circle' : 'mdi-circle-outline'" 
                    :color="isProfessor ? (tipoReserva === 'fixa' ? 'primary' : 'grey-4') : 'grey-4'" 
                    size="sm" 
                  />
                </div>
                <div class="text-caption" :class="isProfessor ? 'text-grey-6' : 'text-grey-4'">
                  Recorrente semestral. <span v-if="!isProfessor" class="text-negative text-weight-bold">Apenas Professores.</span>
                </div>
              </div>
            </q-card>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-7">
        <div class="text-subtitle2 text-weight-bold q-mb-sm text-grey-8">O QUE VOCÊ QUER RESERVAR?</div>
        <!-- Ajuste para os cards de recurso também terem a mesma altura -->
        <div class="row q-col-gutter-sm items-stretch">
          <div class="col-4" v-for="recurso in ['Sala Completa', 'Computador', 'Apenas Mesa']" :key="recurso">
            <q-card bordered class="cursor-pointer q-pa-sm full-height column justify-between" :class="tipoRecurso === recurso ? 'border-primary bg-blue-1' : 'border-grey'" @click="toggleTipoRecurso(recurso)">
              <div>
                <div class="row justify-between items-center q-mb-xs">
                  <div class="text-weight-bold" :class="tipoRecurso === recurso ? 'text-primary' : 'text-grey-8'">{{ recurso }}</div>
                  <q-icon :name="tipoRecurso === recurso ? 'mdi-check-circle' : 'mdi-circle-outline'" :color="tipoRecurso === recurso ? 'primary' : 'grey-4'" size="sm" />
                </div>
                <div class="text-caption text-grey-6">Descrição da opção selecionada.</div>
              </div>
            </q-card>
          </div>
        </div>
        

        <!-- Botão para abrir o Mapa de Computadores -->
        <div v-if="tipoRecurso === 'Computador'" class="q-mt-md animate-fade">
          <q-btn 
            outline 
            color="primary" 
            icon="mdi-desktop-classic" 
            :label="recursoSelecionado ? 'Trocar Computador' : 'Ver Mapa de Computadores'" 
            class="full-width q-py-sm text-weight-bold" 
            @click="modalComputadores = true" 
          />
          <div v-if="recursoSelecionado" class="text-center q-mt-sm text-positive text-weight-bold">
            <q-icon name="mdi-check-circle" /> Máquina selecionada!
          </div>
        </div>

        <!-- POPUP: Mapa Visual de Computadores -->
        <q-dialog v-model="modalComputadores">
          <q-card style="width: 800px; max-width: 95vw; border-radius: 12px;">
            <q-card-section class="row items-center q-pb-none">
              <div class="text-h6 text-weight-bold text-grey-8">Mapa do Laboratório</div>
              <q-space />
              <q-btn icon="mdi-close" flat round dense v-close-popup />
            </q-card-section>

            <q-card-section>
              <!-- Legenda -->
              <div class="row justify-center q-gutter-x-lg q-mb-lg text-caption text-weight-bold">
                <div class="row items-center"><q-icon name="mdi-desktop-classic" color="positive" size="sm" class="q-mr-xs"/> Disponível</div>
                <div class="row items-center"><q-icon name="mdi-desktop-classic" color="negative" size="sm" class="q-mr-xs"/> Em Manutenção</div>
                <div class="row items-center"><q-icon name="mdi-desktop-classic" color="primary" size="sm" class="q-mr-xs"/> Seu PC</div>
              </div>

              <!-- Grade de Computadores (Mini Ícones) -->
              <div class="row q-col-gutter-sm justify-center">
                <div class="col-3 col-sm-2 text-center" v-for="pc in todosComputadores" :key="pc.id">
                  <q-card
                    flat bordered
                    class="q-pa-sm transition-all"
                    :class="getFundoComputador(pc)"
                    @click="selecionarComputador(pc)"
                    style="border-radius: 8px;"
                  >
                    <q-icon name="mdi-desktop-classic" size="lg" :color="getCorComputador(pc)" />
                    <div class="text-caption q-mt-xs text-weight-bold" style="font-size: 11px;">
                      {{ pc.identificacao }}
                    </div>
                  </q-card>
                </div>
              </div>
            </q-card-section>

            <q-card-actions align="center" class="q-pa-md">
              <q-btn unelevated color="primary" label="Confirmar Seleção" v-close-popup class="q-px-xl" />
            </q-card-actions>
          </q-card>
        </q-dialog>

        <!-- Botão para abrir o Mapa de Mesas -->
        <div v-if="tipoRecurso === 'Apenas Mesa'" class="q-mt-md animate-fade">
          <q-btn 
            outline 
            color="primary" 
            icon="mdi-desk" 
            :label="recursoSelecionado ? 'Trocar Mesa' : 'Ver Mapa de Mesas'" 
            class="full-width q-py-sm text-weight-bold" 
            @click="modalMesas = true" 
          />
          <div v-if="recursoSelecionado" class="text-center q-mt-sm text-positive text-weight-bold">
            <q-icon name="mdi-check-circle" /> Mesa selecionada!
          </div>
        </div>

        <!-- POPUP: Mapa Visual de Mesas -->
        <q-dialog v-model="modalMesas">
          <q-card style="width: 800px; max-width: 95vw; border-radius: 12px;">
            <q-card-section class="row items-center q-pb-none">
              <div class="text-h6 text-weight-bold text-grey-8">Mapa de Mesas</div>
              <q-space />
              <q-btn icon="mdi-close" flat round dense v-close-popup />
            </q-card-section>

            <q-card-section>
              <!-- Legenda -->
              <div class="row justify-center q-gutter-x-lg q-mb-lg text-caption text-weight-bold">
                <div class="row items-center"><q-icon name="mdi-desk" color="positive" size="sm" class="q-mr-xs"/> Disponível</div>
                <div class="row items-center"><q-icon name="mdi-desk" color="negative" size="sm" class="q-mr-xs"/> Ocupada / Indisponível</div>
                <div class="row items-center"><q-icon name="mdi-desk" color="primary" size="sm" class="q-mr-xs"/> Sua Mesa</div>
              </div>

              <!-- Grade de Mesas (Mini Ícones) -->
              <div class="row q-col-gutter-sm justify-center">
                <div class="col-3 col-sm-2 text-center" v-for="mesa in todasMesas" :key="mesa.id">
                  <q-card
                    flat bordered
                    class="q-pa-sm transition-all"
                    :class="getFundoComputador(mesa)"
                    @click="selecionarComputador(mesa)"
                    style="border-radius: 8px;"
                  >
                    <q-icon name="mdi-desk" size="lg" :color="getCorComputador(mesa)" />
                    <div class="text-caption q-mt-xs text-weight-bold" style="font-size: 11px;">
                      {{ mesa.identificacao }}
                    </div>
                  </q-card>
                </div>
              </div>
            </q-card-section>

            <q-card-actions align="center" class="q-pa-md">
              <q-btn unelevated color="primary" label="Confirmar Seleção" v-close-popup class="q-px-xl" />
            </q-card-actions>
          </q-card>
        </q-dialog>
      </div>
    </div>

    <!-- Seção: Horários -->
    <div class="text-subtitle2 text-weight-bold q-mb-md text-grey-8 row justify-between items-center">
      ESCOLHA O HORÁRIO PARA ESTA SEMANA
      <div class="row q-gutter-x-sm text-caption">
        <div class="row items-center"><div style="width:12px;height:12px;" class="border-green bg-green-1 q-mr-xs"></div> Disponível</div>
        <div class="row items-center"><div style="width:12px;height:12px;" class="bg-grey-3 q-mr-xs"></div> Ocupado</div>
        <div class="row items-center"><div style="width:12px;height:12px;" class="border-primary bg-blue-1 q-mr-xs"></div> Selecionado</div>
      </div>
    </div>
    
   <q-markup-table flat bordered class="q-mb-xl text-center table-fixed">
      <thead>
        <tr class="bg-grey-1 text-grey-8 text-weight-bold">
          <th style="width: 15%">Horário</th>
          <th v-for="dia in diasSemana" :key="dia" style="width: 17%">{{ dia }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="horario in blocosHorario" :key="horario">
          <td class="text-weight-bold">{{ horario }}</td>
          
          <td v-for="dia in diasSemana" :key="dia">
            <!-- Box wrapper para garantir mesma altura independente do estado -->
            <div class="box-horario">
              <q-card 
                v-if="checkSelecionado(dia, horario)" 
                flat bordered 
                class="border-primary bg-blue-1 cursor-pointer full-width full-height flex flex-center"
                @click="toggleHorario(dia, horario)"
              >
                <div class="text-primary text-weight-bold text-caption">Selecionado</div>
              </q-card>

              <q-btn 
                v-else 
                outline 
                color="positive" 
                label="Livre" 
                class="full-width full-height bg-green-1 text-caption text-weight-bold" 
                padding="none"
                @click="toggleHorario(dia, horario)"
              />
            </div>
          </td>
        </tr>
      </tbody>
    </q-markup-table>

    <!-- Seção: Motivo -->
    <q-card flat bordered class="q-pa-lg q-mb-xl">
      <div class="text-subtitle2 text-weight-bold q-mb-md text-grey-8">MOTIVO DA RESERVA</div>
      <div class="row q-col-gutter-md">
        <div class="col-12 col-md-4">
          <div class="text-body2 q-mb-xs">Selecione a Finalidade</div>
          <q-select 
            outlined 
            dense 
            v-model="motivo" 
            :options="opcoesMotivo"
            label="Escolha um motivo..." 
          />
        </div>
        <div class="col-12 col-md-8">
          <div class="text-body2 q-mb-xs">Descrição do Trabalho (Opcional)</div>
          <q-input outlined dense v-model="descricao" placeholder="Ex: Desenvolvimento de projeto da matéria de SO" />
        </div>
      </div>
    </q-card>

    <!-- Footer Fixo (Dinâmico) -->
    <q-card flat bordered class="bg-grey-1 q-pa-md row justify-between items-center">
      <div class="row items-center">
        <q-icon name="mdi-calendar-check" size="md" color="primary" class="bg-white q-pa-sm rounded-borders q-mr-md shadow-1" />
        <div>
          <div class="text-caption text-grey-6">RESUMO DO AGENDAMENTO</div>
          <div class="text-subtitle1 text-weight-bold">
            {{ labAtual ? labAtual.nome : 'Aguardando laboratório...' }} 
            <span class="q-mx-sm text-grey-4">|</span> 
            <span class="text-primary">{{ resumoHorarios }}</span>
          </div>
        </div>
      </div>
      <q-btn unelevated color="primary" size="lg" label="Confirmar Reserva" class="q-px-xl" @click="salvarReserva" :loading="carregando" />
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSupabase } from '../boot/supabase'
import useReservasApi from '../composables/UseReservaApi'

const { supabase } = useSupabase()
const { listarLaboratorios, criarReserva } = useReservasApi()
const router = useRouter()
const route = useRoute()

defineOptions({ name: 'ReservaPage' })

// Variáveis da interface
const tipoReserva = ref(null)
const tipoRecurso = ref(null)
const motivo = ref(null)
const descricao = ref('')
const isProfessor = ref(false)

// Lista de opções de motivo que muda consoante o perfil
const opcoesMotivo = computed(() => {
  const opcoesComuns = ['Trabalho em grupo', 'Estudo individual', 'Projeto de Extensão']
  
  if (isProfessor.value) {
    return ['Sala de Aula', 'Horário de permanência', ...opcoesComuns]
  }
  
  return opcoesComuns
})

// Controle de estado do Popup
const modalComputadores = ref(false)
const modalMesas = ref(false)
const recursoSelecionado = ref(null)

// Pega todas as Mesas do laboratório (Filtra onde a palavra 'mesa' aparece no tipo)
const todasMesas = computed(() => {
  if (!labAtual.value || !labAtual.value.recursos) return []
  return labAtual.value.recursos
    .filter(r => r.tipo.toLowerCase().includes('mesa'))
    .sort((a, b) => a.identificacao.localeCompare(b.identificacao)) 
})

// Pega todos os PCs do laboratório para desenhar a grade, organizados por ordem alfabética (PC 01, PC 02...)
const todosComputadores = computed(() => {
  if (!labAtual.value || !labAtual.value.recursos) return []
  return labAtual.value.recursos
    .filter(r => r.tipo.toLowerCase() === 'computador')
    .sort((a, b) => a.identificacao.localeCompare(b.identificacao)) 
})

// Função ao clicar no mini-computador
// --- FUNÇÕES DE SELEÇÃO E DESMARCAÇÃO ---

// Alterna o Tipo de Reserva
const toggleTipoReserva = (tipo) => {
  if (tipo === 'fixa' && !isProfessor.value) return // Bloqueia alunos
  tipoReserva.value = tipoReserva.value === tipo ? null : tipo
}

// Alterna o Tipo de Recurso (limpando a máquina se desmarcar)
const toggleTipoRecurso = (recurso) => {
  if (tipoRecurso.value === recurso) {
    tipoRecurso.value = null
    recursoSelecionado.value = null
  } else {
    tipoRecurso.value = recurso
    recursoSelecionado.value = null
  }
}

// Alterna a seleção de um PC ou Mesa no mapa
const selecionarComputador = (item) => {
  if (item.status !== 'disponivel') return // Impede de selecionar estragados
  // Se clicar no mesmo que já estava selecionado, limpa (null). Se não, seleciona o novo.
  recursoSelecionado.value = recursoSelecionado.value === item.id ? null : item.id
}

// Controla a cor do ícone no mapa
const getCorComputador = (pc) => {
  if (recursoSelecionado.value === pc.id) return 'white'
  if (pc.status === 'disponivel') return 'positive' // Verde
  return 'negative' // Vermelho
}

// Controla a cor do fundo e a borda do card do computador
const getFundoComputador = (pc) => {
  if (recursoSelecionado.value === pc.id) return 'bg-primary text-white cursor-pointer shadow-3'
  if (pc.status === 'disponivel') return 'bg-white border-grey cursor-pointer'
  return 'bg-grey-2 border-grey text-grey-5 cursor-not-allowed' // Bloqueado
}

// Dados da Grade Semanal
const diasSemana = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta']
const blocosHorario = ['13:30 - 15:30', '15:30 - 17:30', '19:00 - 21:00', '21:00 - 23:00']
const horariosSelecionados = ref([])

const toggleHorario = (dia, horario) => {
  const identificador = `${dia}|${horario}`
  const index = horariosSelecionados.value.indexOf(identificador)
  if (index > -1) horariosSelecionados.value.splice(index, 1)
  else horariosSelecionados.value.push(identificador)
}

const resumoHorarios = computed(() => {
  if (horariosSelecionados.value.length === 0) return 'Nenhum horário selecionado'
  if (horariosSelecionados.value.length === 1) {
    const [dia, hora] = horariosSelecionados.value[0].split('|')
    return `${dia}-feira | ${hora}`
  }
  return `${horariosSelecionados.value.length} horários selecionados`
})

const checkSelecionado = (dia, horario) => horariosSelecionados.value.includes(`${dia}|${horario}`)

const laboratorioId = route.params.laboratorioId
const dadosLaboratorio = ref([])
const carregando = ref(false)

const labAtual = computed(() => {
  if (!dadosLaboratorio.value || dadosLaboratorio.value.length === 0) return null
  
  return dadosLaboratorio.value.find(lab => 
    String(lab.id) === String(laboratorioId) || 
    lab.nome.includes(`0${laboratorioId}`) || 
    lab.nome.includes(String(laboratorioId))
  )
})

// --- CARREGAR OS DADOS ---
const carregarLaboratorio = async () => {
  try {
    carregando.value = true
    
    // Pega o utilizador logado no Auth
    const { data: { user } } = await supabase.auth.getUser()
    
    if (user) {
      const { data: perfilData, error: perfilError } = await supabase
        .from('usuarios')
        .select('perfil')
        .eq('id', user.id)
        .single()
        
      if (!perfilError && perfilData) {
        isProfessor.value = perfilData.perfil === 'professor'
      }
    }

    const data = await listarLaboratorios()
    dadosLaboratorio.value = Array.isArray(data) ? data : (data.data || data.laboratorios || [])
    
  } catch (error) {
    console.error("Falha ao buscar laboratórios:", error)
  } finally {
    carregando.value = false
  }
}

// --- SALVAR A RESERVA NO BANCO ---
const salvarReserva = async () => {
  if (!tipoReserva.value) {
    alert("Por favor, selecione o Tipo de Reserva (Normal ou Fixa).")
    return
  }

  if (!tipoRecurso.value) {
    alert("Por favor, selecione o que deseja reservar (Sala, Computador ou Mesa).")
    return
  }

  if (horariosSelecionados.value.length === 0) {
    alert("Selecione pelo menos um horário na grade.")
    return
  }
  
  if ((tipoRecurso.value === 'Computador' || tipoRecurso.value === 'Apenas Mesa') && !recursoSelecionado.value) {
    alert(`Por favor, abra o mapa e selecione um(a) ${tipoRecurso.value.toLowerCase()}.`)
    return
  }

  if (!motivo.value) {
    alert("Por favor, selecione o motivo da reserva.")
    return
  }

  try {
    carregando.value = true
    
    // Prepara o pacote de dados para enviar ao PHP
    const payload = {
      laboratorioId: laboratorioId,
      tipoReserva: tipoReserva.value,
      tipoRecurso: tipoRecurso.value,
      recursoId: recursoSelecionado.value,
      motivo: motivo.value,
      descricao: descricao.value,
      horariosSelecionados: horariosSelecionados.value
    }

    // Envia para o banco usando a nossa função do UseReservasApi
    await criarReserva(payload)
    
    alert("Reserva efetuada com sucesso!")
    router.push('/') // Volta para a página inicial
    
  } catch (error) {
    console.error("Erro ao salvar reserva:", error)
    alert("Ocorreu um erro ao tentar salvar a reserva.")
  } finally {
    carregando.value = false
  }
}

onMounted(() => {
  carregarLaboratorio()
})
</script>

<style scoped>
.border-primary { border: 2px solid var(--q-primary); }
.border-grey { border: 1px solid #e0e0e0; }
.border-green { border: 1px solid var(--q-positive); }

/* Ajusta layout da tabela e dos botões de horário para manterem a mesma altura/largura */
.table-fixed {
  table-layout: fixed;
  width: 100%;
}
.box-horario {
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.box-horario .q-btn,
.box-horario .q-card {
  min-height: 100%;
  margin: 0;
}
</style>