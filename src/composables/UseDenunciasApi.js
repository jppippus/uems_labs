import useApi from './UseApi'

export default function useDenunciasApi() {
  const { apiPost } = useApi()

  const criarDenuncia = (payload) => apiPost('/denuncias/criar.php', payload)

  return { criarDenuncia }
}
