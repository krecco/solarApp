import axios from '@axios'

export default {
  namespaced: true,
  state: {
    updatedMsgCountAt: 0,
  },
  getters: {
    getUpdatedMsgCountAt: state => state.updatedMsgCountAt,
  },
  mutations: {
    updateUpdatedMsgCountAt: state => {
      state.updatedMsgCountAt = new Date().getTime()
    },
  },
  actions: {
    countNewMessages() {
      return new Promise((resolve, reject) => {
        axios
          .get('/message/unread-frontend')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
