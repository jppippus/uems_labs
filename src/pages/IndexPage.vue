<template>
  <q-page padding class="q-pa-xl">
    <div class="row justify-between items-center q-mb-lg">
      <div>
        <div class="text-h4 text-weight-bold text-grey-9">Bem-vindo, Acadêmico</div>
        <div class="text-subtitle1 text-grey-6">Hoje, 12 de Março de 2026</div>
      </div>
      <q-btn flat round icon="mdi-bell-outline" color="grey-8" class="bg-white shadow-1" />
    </div>

    <div class="row q-col-gutter-md q-mb-xl">
      <div class="col-12 col-md-6">
        <q-card class="shadow-1 q-pa-md" style="border-radius: 12px;">
          <q-card-section>
            <div class="row justify-between items-center">
              <div class="text-subtitle2 text-grey-7 text-weight-bold">Reservas Ativas</div>
              <q-icon name="mdi-calendar-range" size="sm" color="primary" class="bg-blue-1 q-pa-xs rounded-borders" />
            </div>
            <div class="text-h3 text-weight-bold q-mt-sm">2</div>
            <div class="text-caption text-grey-6 q-mt-sm">reservas agendadas para esta semana</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-6">
        <q-card class="shadow-1 q-pa-md" style="border-radius: 12px;">
          <q-card-section>
            <div class="row justify-between items-center">
              <div class="text-subtitle2 text-grey-7 text-weight-bold">Próxima Reserva</div>
              <q-icon name="mdi-clock-outline" size="sm" color="primary" class="bg-blue-1 q-pa-xs rounded-borders" />
            </div>
            <div class="text-h5 text-weight-bold q-mt-md">Lab 03 - Hoje, 14:00</div>
            <div class="text-caption text-grey-6 q-mt-xs">Bloco C • 20 computadores</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <div class="row justify-between items-center q-mb-md">
      <div class="text-h6 text-weight-bold">Laboratórios Disponíveis</div>
      <a href="#" class="text-grey-8" style="text-decoration: underline;">Ver todos</a>
    </div>

    <div class="row q-col-gutter-md">
      <!-- Exemplo Iterável: Laboratório 01 -->
      <div class="col-12 col-md-6" v-for="n in 4" :key="n">
        <q-card class="shadow-1" style="border-radius: 12px;">
          <q-card-section>
            <div class="row justify-between items-center q-mb-sm">
              <div class="text-h6 text-weight-bold">Laboratório 0{{ n }}</div>
              <q-badge :color="n === 2 ? 'negative' : 'positive'" rounded transparent class="q-px-sm q-py-xs">
                <q-icon name="mdi-circle" size="8px" class="q-mr-xs" />
                {{ n === 2 ? 'Ocupado' : 'Disponível' }}
              </q-badge>
            </div>
            <div class="text-body2 text-grey-7 row items-center q-mb-md">
              <q-icon name="mdi-map-marker-outline" size="xs" class="q-mr-xs" />
              Bloco A • 30 computadores
            </div>
            <q-separator />
            <div class="row justify-between items-center q-mt-md">
              <div class="text-caption text-grey-6">Horário: 08:00-18:00</div>
              <q-btn 
                unelevated 
                :outline="n === 2"
                :color="n === 2 ? 'grey-8' : 'primary'" 
                :label="n === 2 ? 'Ver horário' : 'Reservar'" 
                class="q-px-md" 
                style="border-radius: 6px;"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'IndexPage',
  setup() {
    return {}
  }
})
</script>