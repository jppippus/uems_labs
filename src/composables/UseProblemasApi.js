import useApi from './UseApi'

export default function useProblemasApi() {
  const { apiGet, apiPost } = useApi()

  const getLaboratorios = () => apiGet('/laboratorios/listar.php')

  const relatarProblema = (payload) => apiPost('/problemas/relatar.php', payload)

  return { getLaboratorios, relatarProblema }
}
