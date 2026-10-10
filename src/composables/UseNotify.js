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

  const notifyError = (message) => {
    $q.notify({
      type: 'negative',
      message: message || 'Erro na operação.',
      position: 'top-right'
    })
  }

  return {
    notifySuccess, 
    notifyError
  }
}