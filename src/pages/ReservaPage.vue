<template>
  <q-page padding class="q-pa-xl">
    <q-breadcrumbs class="text-grey-7 q-mb-md">
      <q-breadcrumbs-el label="Início" to="/" />
      <q-breadcrumbs-el label="Laboratórios" />
      <q-breadcrumbs-el label="Laboratório 01" />
      <q-breadcrumbs-el label="Reservar" class="text-primary text-weight-bold" />
    </q-breadcrumbs>

    <div class="row justify-between items-center q-mb-lg">
      <div>
        <div class="text-h4 text-weight-bold text-grey-9">Reservar Laboratório 01</div>
        <div class="text-subtitle2 text-grey-6 row items-center q-mt-xs">
          <q-icon name="mdi-map-marker" size="sm" class="q-mr-xs" />
          Bloco A <span class="q-mx-sm">•</span> 30 Computadores
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
          <th>Seg<br><span class="text-caption text-weight-light">16/03</span></th>
          <th>Ter<br><span class="text-caption text-weight-light">17/03</span></th>
          <th>Qua<br><span class="text-caption text-weight-light">18/03</span></th>
          <th>Qui<br><span class="text-caption text-weight-light">19/03</span></th>
          <th>Sex<br><span class="text-caption text-weight-light">20/03</span></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="text-weight-bold">13:30 - 15:30</td>
          <td><q-btn outline color="positive" label="Livre" class="full-width bg-green-1" size="sm" /></td>
          <td><q-btn outline color="positive" label="Livre" class="full-width bg-green-1" size="sm" /></td>
          <td>
            <q-card flat bordered class="border-primary bg-blue-1 q-pa-xs">
              <div class="text-primary text-weight-bold text-caption">Selecionado</div>
              <div class="text-primary text-caption" style="font-size: 10px;">SEU HORÁRIO</div>
            </q-card>
          </td>
          <td class="bg-grey-2 text-grey-6 text-caption">Indisponível</td>
          <td><q-btn outline color="positive" label="Livre" class="full-width bg-green-1" size="sm" /></td>
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

    <!-- Footer Fixo (Simulação) -->
    <q-card flat bordered class="bg-grey-1 q-pa-md row justify-between items-center">
      <div class="row items-center">
        <q-icon name="mdi-calendar-check" size="md" color="primary" class="bg-white q-pa-sm rounded-borders q-mr-md shadow-1" />
        <div>
          <div class="text-caption text-grey-6">RESUMO DO AGENDAMENTO</div>
          <div class="text-subtitle1 text-weight-bold">
            Laboratório 01 <span class="q-mx-sm text-grey-4">|</span> 
            <span class="text-primary">Quarta-feira, 18 de Março</span> <span class="q-mx-sm text-grey-4">|</span> 
            13:30 - 15:30
          </div>
        </div>
      </div>
      <q-btn unelevated color="primary" size="lg" label="Confirmar Reserva" class="q-px-xl" />
    </q-card>
  </q-page>
</template>

<script>
import { defineComponent, ref } from 'vue'

export default defineComponent({
  name: 'ReservaPage',
  setup() {
    const tipoReserva = ref('normal')
    const tipoRecurso = ref('Computador')
    const motivo = ref('Trabalho em grupo')
    const descricao = ref('')

    return { tipoReserva, tipoRecurso, motivo, descricao }
  }
})
</script>

<style scoped>
.border-primary { border: 2px solid var(--q-primary); }
.border-grey { border: 1px solid #e0e0e0; }
.border-green { border: 1px solid var(--q-positive); }
</style>