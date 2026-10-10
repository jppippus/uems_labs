import { useSupabase } from 'src/boot/supabase'
import useAuthUser from 'src/composables/UseAuthUser'

export default function useReservaApi() {
  const { supabase } = useSupabase()
  const { user } = useAuthUser()

  // Buscar todos os laboratórios e seus recursos (Salas, Mesas, PCs)
  const getLaboratoriosComRecursos = async () => {
    const { data, error } = await supabase
      .from('laboratorios')
      .select(`
        id, 
        nome, 
        localizacao,
        recursos (
          id,
          tipo,
          identificacao,
          status
        )
      `)
    
    if (error) throw error
    return data
  }

  // Criar uma nova reserva
  const registrarReserva = async (form) => {
    const { data, error } = await supabase.from('reservas').insert([
      {
        ...form,
        usuario_id: user.value.id, // O usuário logado é pego diretamente do Auth
        status: 'ativa'
      },
    ])

    if (error) throw error
    return data
  }

  // Buscar reservas ativas do dia (Para o Dashboard do Técnico)
  const getReservasDoDia = async (dataFiltro) => {
    const { data, error } = await supabase
      .from('reservas')
      .select(`
        *,
        recursos (identificacao, tipo),
        usuarios (nome, rgm)
      `)
      .eq('data_reserva', dataFiltro)
      .eq('status', 'ativa')

    if (error) throw error
    return data
  }

  // Relatar um problema (Ativará a Trigger no banco para indisponibilizar o PC)
  const relatarProblema = async (form) => {
    const { data, error } = await supabase.from('problemas_relatados').insert([
      form
    ])

    if (error) throw error
    return data
  }

  return {
    getLaboratoriosComRecursos,
    registrarReserva,
    getReservasDoDia,
    relatarProblema
  }
}