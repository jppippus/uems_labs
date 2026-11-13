import { ref } from 'vue'
import { useSupabase } from '../boot/supabase'

// Mantém o estado do usuário de forma global
const user = ref(null)
// Perfil (aluno/professor/tecnico) da tabela "usuarios" — usado pro
// redirecionamento e pelas guardas de rota por perfil.
const perfil = ref(null)
// Nome de exibição (tabela "usuarios") — usado na sidebar, evita cada
// tela ter que buscar isso de novo via Supabase direto.
const nome = ref(null)

export default function useAuthUser() {
  const { supabase } = useSupabase()

  const carregarPerfil = async (userId) => {
    const { data, error } = await supabase
      .from('usuarios')
      .select('rgm, nome, perfil')
      .eq('id', userId)
      .single()
    if (error) throw error

    perfil.value = data.perfil
    nome.value = data.nome
    return data
  }

  const login = async ({ email, password }) => {
    const { data, error } = await supabase.auth.signInWithPassword({
      email,
      password,
    })
    if (error) throw error

    user.value = data.user
    await carregarPerfil(data.user.id)
    return data.user
  }

  const logout = async () => {
    const { error } = await supabase.auth.signOut()
    if (error) throw error

    user.value = null
    perfil.value = null
    nome.value = null
  }

  const isLoggedIn = () => {
    return !!user.value
  }

  // Garante que user/perfil estejam populados a partir da sessão já
  // existente (ex: usuário já logado antes, dando F5 na página) — a guarda
  // de rota chama isso em vez de supabase.auth.getSession() direto.
  const sincronizarSessao = async () => {
    const { data: { session } } = await supabase.auth.getSession()

    if (!session) {
      user.value = null
      perfil.value = null
      nome.value = null
      return null
    }

    if (!user.value || user.value.id !== session.user.id) {
      user.value = session.user
      await carregarPerfil(session.user.id)
    }

    return session
  }

  return {
    user,
    perfil,
    nome,
    login,
    logout,
    isLoggedIn,
    sincronizarSessao,
  }
}