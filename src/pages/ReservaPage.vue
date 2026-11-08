<template>
  <q-page padding class="q-pa-xl">
    <q-breadcrumbs class="text-grey-7 q-mb-md">
      <q-breadcrumbs-el label="Início" to="/" />
      <q-breadcrumbs-el label="Laboratórios" />
      <!-- Substituímos o texto fixo pelo nome dinâmico -->
      <q-breadcrumbs-el :label="labAtual ? labAtual.nome : 'Carregando...'" />
      <q-breadcrumbs-el label="Reservar" class="text-primary text-weight-bold" />
    </q-breadcrumbs>

    <div class="row justify-between items-center q-mb-lg">
      <div>
        <!-- Mostra o nome real do laboratório ou 'Carregando...' enquanto a API não responde -->
        <div class="text-h4 text-weight-bold text-grey-9">
          Reservar {{ labAtual ? labAtual.nome : '...' }}
        </div>
        
        <div class="text-subtitle2 text-grey-6 row items-center q-mt-xs">
          <q-icon name="mdi-map-marker" size="sm" class="q-mr-xs" />
          <!-- Mostra o bloco e a capacidade reais que vieram do banco -->
          {{ labAtual ? labAtual.bloco : '...' }} 
          <span class="q-mx-sm">•</span> 
          {{ labAtual ? labAtual.capacidade : '...' }} Computadores
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
        <div class="row q-col-gutter-sm">
          <div class="col-6">
            <q-card bordered class="cursor-pointer q-pa-sm" :class="tipoReserva === 'normal' ? 'border-primary bg-blue-1' : 'border-grey'" @click="tipoReserva = 'normal'">
              <div class="row justify-between items-center q-mb-xs">
                <div class="text-weight-bold text-primary">Reserva Normal</div>
                <q-icon :name="tipoReserva === 'normal' ? 'mdi-check-circle' : 'mdi-circle-outline'" :color="tipoReserva === 'normal' ? 'primary' : 'grey-4'" size="sm" />
              </div>
              <div class="text-caption text-grey-6">Sessão única de uso imediato ou agendado para a semana.</div>
            </q-card>
          </div>
          <div class="col-6">
            <q-card bordered class="cursor-pointer q-pa-sm border-grey">
              <div class="row justify-between items-center q-mb-xs">
                <div class="text-weight-bold text-grey-8">Reserva Fixa</div>
                <q-icon name="mdi-circle-outline" color="grey-4" size="sm" />
              </div>
              <div class="text-caption text-grey-5">Recorrente semestral. Disponível apenas para Professores.</div>
            </q-card>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-7">
        <div class="text-subtitle2 text-weight-bold q-mb-sm text-grey-8">O QUE VOCÊ QUER RESERVAR?</div>
        <div class="row q-col-gutter-sm">
          <div class="col-4" v-for="recurso in ['Sala Completa', 'Computador', 'Apenas Mesa']" :key="recurso">
            <q-card bordered class="cursor-pointer q-pa-sm" :class="tipoRecurso === recurso ? 'border-primary bg-blue-1' : 'border-grey'" @click="tipoRecurso = recurso">
              <div class="row justify-between items-center q-mb-xs">
                <div class="text-weight-bold" :class="tipoRecurso === recurso ? 'text-primary' : 'text-grey-8'">{{ recurso }}</div>
                <q-icon :name="tipoRecurso === recurso ? 'mdi-check-circle' : 'mdi-circle-outline'" :color="tipoRecurso === recurso ? 'primary' : 'grey-4'" size="sm" />
              </div>
              <div class="text-caption text-grey-6">Descrição da opção selecionada.</div>
            </q-card>
          </div>
        </div>
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
    
   <q-markup-table flat bordered class="q-mb-xl text-center">
      <thead>
        <tr class="bg-grey-1 text-grey-8 text-weight-bold">
          <th>Horário</th>
          <!-- Cria as colunas dos dias dinamicamente -->
          <th v-for="dia in diasSemana" :key="dia">{{ dia }}</th>
        </tr>
      </thead>
      <tbody>
        <!-- Cria as linhas dos horários dinamicamente -->
        <tr v-for="horario in blocosHorario" :key="horario">
          <td class="text-weight-bold">{{ horario }}</td>
          
          <!-- Cria as células cruzando dia x horário -->
          <td v-for="dia in diasSemana" :key="dia">
            
            <!-- Se estiver SELECIONADO, mostra o card azul -->
            <q-card 
              v-if="checkSelecionado(dia, horario)" 
              flat bordered 
              class="border-primary bg-blue-1 q-pa-xs cursor-pointer"
              @click="toggleHorario(dia, horario)"
            >
              <div class="text-primary text-weight-bold text-caption">Selecionado</div>
              <div class="text-primary text-caption" style="font-size: 10px;">SEU HORÁRIO</div>
            </q-card>

            <!-- Se estiver LIVRE, mostra o botão verde -->
            <q-btn 
              v-else 
              outline 
              color="positive" 
              label="Livre" 
              class="full-width bg-green-1" 
              size="sm" 
              @click="toggleHorario(dia, horario)"
            />

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
          <q-select outlined dense v-model="motivo" :options="['Trabalho em grupo', 'Estudo individual', 'Projeto de Extensão']" />
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
            <!-- Puxa o nome real do laboratório da variável labAtual -->
            {{ labAtual ? labAtual.nome : 'Aguardando laboratório...' }} 
            <span class="q-mx-sm text-grey-4">|</span> 
            
            <!-- Mostra o resultado da função computed que criámos -->
            <span class="text-primary">{{ resumoHorarios }}</span>
          </div>
        </div>
      </div>
      <q-btn unelevated color="primary" size="lg" label="Confirmar Reserva" class="q-px-xl" />
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

import { useRoute } from 'vue-router'
import { supabase } from 'src/boot/supabase'

// Define o nome do componente (opcional no <script setup>, mas bom manter)
defineOptions({
  name: 'ReservaPage'
})

// Variáveis da interface (que antes estavam no export default)
const tipoReserva = ref('normal')
const tipoRecurso = ref('Computador')
const motivo = ref('Trabalho em grupo')
const descricao = ref('')

// --- DADOS DA GRADE SEMANAL ---
// Estrutura de dias e blocos de horários da instituição
const diasSemana = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta']
const blocosHorario = ['13:30 - 15:30', '15:30 - 17:30', '19:00 - 21:00', '21:00 - 23:00']

// Array que vai guardar os horários que o usuário clicar na tela
const horariosSelecionados = ref([])

// Função que marca ou desmarca um horário quando o usuário clica
const toggleHorario = (dia, horario) => {
  const identificador = `${dia}|${horario}`
  const index = horariosSelecionados.value.indexOf(identificador)
  
  if (index > -1) {
    // Se já estava selecionado, remove (desmarca)
    horariosSelecionados.value.splice(index, 1)
  } else {
    // Se não estava, adiciona na lista
    horariosSelecionados.value.push(identificador)
  }
}

// --- RESUMO DO RODAPÉ ---
const resumoHorarios = computed(() => {
  if (horariosSelecionados.value.length === 0) {
    return 'Nenhum horário selecionado'
  }
  
  if (horariosSelecionados.value.length === 1) {
    // Pega o formato "Quarta|13:30 - 15:30" e separa em duas variáveis
    const [dia, hora] = horariosSelecionados.value[0].split('|')
    return `${dia}-feira | ${hora}`
  }
  
  // Se o utilizador selecionar mais do que um bloco na tabela
  return `${horariosSelecionados.value.length} horários selecionados`
})

// Função para o Vue saber se pinta o botão de azul (selecionado) ou verde (livre)
const checkSelecionado = (dia, horario) => {
  return horariosSelecionados.value.includes(`${dia}|${horario}`)
}

// 1. Inicializa o objeto da rota atual
const route = useRoute()

// 2. Captura o parâmetro exato que você definiu no routes.js
const laboratorioId = route.params.laboratorioId

// Variável para guardar a lista de laboratórios ou os horários
const dadosLaboratorio = ref([])

// Procura na lista que veio da API qual laboratório tem o ID igual ao da URL
const labAtual = computed(() => {
  if (!dadosLaboratorio.value || dadosLaboratorio.value.length === 0) return null
  return dadosLaboratorio.value.find(lab => String(lab.id) === String(laboratorioId))
})

// Variável para controlar o estado de carregamento (útil para mostrar um spinner na tela)
const carregando = ref(false)

const carregarLaboratorio = async () => {
  try {
    carregando.value = true
    
    // 1. Pega a sessão atual do Supabase para obter o token JWT
    const { data: { session }, error: sessionError } = await supabase.auth.getSession()
    
    if (sessionError || !session) {
      console.error('Sessão não encontrada. O usuário precisa estar logado.')
      return
    }

    const token = session.access_token

    // 2. Faz a chamada HTTP para o endpoint do João Pedro
    const response = await fetch('http://localhost/uems-api/api/laboratorios/listar.php', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        // É aqui que o token passa pelo middleware/auth.php do backend
        'Authorization': `Bearer ${token}` 
      }
    })

    // 3. Verifica se o backend devolveu algum erro (status 400, 401, 500...)
    if (!response.ok) {
      throw new Error(`Erro na requisição: ${response.status}`)
    }

    // 4. Converte o resultado para objeto JavaScript
    const data = await response.json()
    
    // 5. Salva o resultado na nossa variável reativa
    dadosLaboratorio.value = data
    console.log("Dados recebidos da API:", dadosLaboratorio.value)

  } catch (error) {
    console.error("Falha ao buscar laboratórios:", error)
  } finally {
    carregando.value = false
  }
}

// O Vue executa o que estiver dentro do onMounted assim que a tela abre
onMounted(() => {
  carregarLaboratorio()
})

</script>

<style scoped>
.border-primary { border: 2px solid var(--q-primary); }
.border-grey { border: 1px solid #e0e0e0; }
.border-green { border: 1px solid var(--q-positive); }
</style>