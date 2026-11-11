import useApi from './UseApi'

export default function useReservasApi() {
  const { apiGet, apiPost } = useApi()

  // Buscar os laboratórios no PHP
  const listarLaboratorios = () => apiGet('/laboratorios/listar.php')

  // Enviar os dados da reserva para o PHP
  const criarReserva = (payload) => apiPost('/reservas/criar.php', payload)

  // NOVA FUNÇÃO: Busca as reservas de um laboratório específico
  const listarReservasPorLaboratorio = (labId) => apiGet(`/reservas/listar.php?laboratorio_id=${labId}`)

  return { listarLaboratorios, criarReserva, listarReservasPorLaboratorio}
}