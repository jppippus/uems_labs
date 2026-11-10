import useApi from './UseApi'

export default function useReservasApi() {
  const { apiGet, apiPost } = useApi()

  // Buscar os laboratórios no PHP
  const listarLaboratorios = () => apiGet('/laboratorios/listar.php')

  // Enviar os dados da reserva para o PHP
  const criarReserva = (payload) => apiPost('/reservas/criar.php', payload)

  return { listarLaboratorios, criarReserva }
}