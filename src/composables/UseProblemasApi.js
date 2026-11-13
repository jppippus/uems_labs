import useApi from './UseApi'

export default function useProblemasApi() {
  const { apiGet, apiPost } = useApi()

  const getLaboratorios = () => apiGet('/laboratorios/listar.php')

  // Reservas ativas do usuário logado, pra escolher de qual relatar um problema.
  const getMinhasReservasAtivas = async () => {
    const dados = await apiGet('/reservas/listar.php')
    return dados.ativas ?? []
  }

  const relatarProblema = (payload) => apiPost('/problemas/relatar.php', payload)

  return { getLaboratorios, getMinhasReservasAtivas, relatarProblema }
}
