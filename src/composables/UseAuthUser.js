import { ref } from 'vue'
import { useSupabase } from '../boot/supabase'

// Mantém o estado do usuário de forma global
const user = ref(null)

export default function useAuthUser() {
  const { supabase } = useSupabase()

  const login = async ({ email, password }) => {
    const { data, error } = await supabase.auth.signInWithPassword({
      email,
      password,
    })
    if (error) throw error
    
    user.value = data.user
    return data.user
  }

  const logout = async () => {
    const { error } = await supabase.auth.signOut()
    if (error) throw error
    
    user.value = null
  }

  const isLoggedIn = () => {
    return !!user.value
  }

  return {
    user,
    login,
    logout,
    isLoggedIn
  }
}