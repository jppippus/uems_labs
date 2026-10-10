import { createClient } from '@supabase/supabase-js'
import { defineBoot } from '#q-app' // Novo import de wrappers da v3

const supabaseUrl = import.meta.env.QCLI_SUPABASE_URL
const supabaseKey = import.meta.env.QCLI_SUPABASE_KEY

const supabase = createClient(supabaseUrl, supabaseKey)

export default defineBoot(({ app }) => {
  app.config.globalProperties.$supabase = supabase
})

export { supabase }
export function useSupabase() {
  return { supabase }
}