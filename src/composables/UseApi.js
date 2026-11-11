import { useSupabase } from '../boot/supabase'

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

    // 1. Lemos a resposta como TEXTO PURO primeiro para não perder erros do PHP
    const textoCru = await response.text()
    console.log(`[UseApi] Resposta crua de ${path}:`, textoCru)

    // 2. Tentamos converter para JSON
    let body = null
    try {
      if (textoCru) {
        body = JSON.parse(textoCru)
      }
    } catch (erro) {
      console.error("Falha ao converter resposta do PHP para JSON:", erro)
      console.error("Texto recebido do PHP:", textoCru)
    }

    // 3. Se houver erro HTTP (400, 500, etc)
    if (!response.ok) {
      const msg = body?.error || body?.mensagem || textoCru.substring(0, 100)
      throw new Error(`Erro na requisição (${response.status}): ${msg}`)
    }
    
    // 4. Se for 200 OK mas não for um JSON válido
    if (body === null && textoCru !== '') {
       throw new Error("O servidor devolveu texto inválido em vez de JSON. Verifique o Console.")
    }

    return body
  }

  const apiGet = (path) => request(path, { method: 'GET' })

  const apiPost = (path, payload) =>
    request(path, { method: 'POST', body: JSON.stringify(payload) })

  return { apiGet, apiPost }
}
