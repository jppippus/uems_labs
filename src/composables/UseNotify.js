import { useQuasar } from 'quasar'

export default function useNotify() {
  const $q = useQuasar()

  const notifySuccess = (message) => {
    $q.notify({
      type: 'positive',
      message: message || 'Sucesso!',
      position: 'top-right'
    })
  }

  const notifyError = (message, detail) => {
    $q.notify({
      type: 'negative',
      message: message || 'Erro na operação.',
      caption: detail || undefined,
      position: 'top-right'
    })
  }

  return {
    notifySuccess, 
    notifyError
  }
}