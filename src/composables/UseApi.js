import { useSupabase } from 'src/boot/supabase'

const API_URL = import.meta.env.QCLI_API_URL

/**
 * Chamada padrão pro backend PHP (uems_labs_api), já anexando o token
 * da sessão do Supabase Auth no header Authorization.
 *
 * Uso dentro de uma composable de domínio, ex: UseReservasApi.js
 *   const { apiGet, apiPost } = useApi()
 *   const laboratorios = await apiGet('/laboratorios/listar.php')
 *   const reserva = await apiPost('/reservas/criar.php', { recurso_id, data_reserva, ... })
 */
export default function useApi() {
  const { supabase } = useSupabase()

  const request = async (path, options = {}) => {
    const {
      data: { session },
    } = await supabase.auth.getSession()

    const response = await fetch(`${API_URL}${path}`, {
      ...options,
      headers: {
        'Content-Type': 'application/json',
        ...(session ? { Authorization: `Bearer ${session.access_token}` } : {}),
        ...options.headers,
      },
    })

    const body = await response.json().catch(() => null)

    if (!response.ok) {
      throw new Error(body?.error || `Erro na requisição (${response.status}).`)
    }

    return body
  }

  const apiGet = (path) => request(path, { method: 'GET' })

  const apiPost = (path, payload) =>
    request(path, { method: 'POST', body: JSON.stringify(payload) })

  return { apiGet, apiPost }
}
