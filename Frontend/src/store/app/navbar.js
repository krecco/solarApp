import axios from '@axios'

export default {
  namespaced: true,
  state: {
    updatedAt: 0,
    updatedMsgCountAt: 0,
  },
  getters: {
    getUpdatedAt: state => state.updatedAt,
    getUpdatedMsgCountAt: state => state.updatedMsgCountAt,
  },
  mutations: {
    updateUpdatedAt: state => {
      state.updatedAt = new Date().getTime()
    },
    updateUpdatedMsgCountAt: state => {
      state.updatedMsgCountAt = new Date().getTime()
    },
  },
  actions: {
    webInfoLatest() {
      return new Promise((resolve, reject) => {
        axios
          .get('/web/info/latest')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    activityLatest() {
      return new Promise((resolve, reject) => {
        axios
          .get('/user/app-activity/latest')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    countNotifications() {
      return new Promise((resolve, reject) => {
        axios
          .get('/user/count-notifications')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    countNewMessages() {
      return new Promise((resolve, reject) => {
        axios
          .get('/message/unread-backend')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
