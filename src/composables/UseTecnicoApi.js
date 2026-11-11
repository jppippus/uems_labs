import useApi from './UseApi'

export default function useTecnicoApi() {
  const { apiGet, apiPost } = useApi()

  const getDashboard = () => apiGet('/tecnico/dashboard.php')

  const getProblemas = () => apiGet('/problemas/listar.php')

  const resolverProblema = (problemaId) =>
    apiPost('/problemas/resolver.php', { problema_id: problemaId })

  return { getDashboard, getProblemas, resolverProblema }
}
