import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchSettings(ctx, { settingsId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/settings/get-settings/${settingsId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    editSettings(ctx, postData) {
      console.log(postData)
      return new Promise((resolve, reject) => {
        axios
          .post('/settings/update-settings', postData.baseData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchList(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/settings/list', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addSetting(ctx, postData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/settings/add', postData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    copySetting(ctx, id) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/settings/copy/${id.id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchListCampaign(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/campaign/list', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addSettingCampaign(ctx, postData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/campaign/add', postData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchSettingsCampaign(ctx, { settingsId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/campaign/get-settings/${settingsId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    editSettingsCampaign(ctx, postData) {
      console.log(postData)
      return new Promise((resolve, reject) => {
        axios
          .post('/campaign/update-settings', postData.baseData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchListExtras(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/extras/list', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addSettingExtras(ctx, postData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/extras/add', postData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchSettingsExtras(ctx, { settingsId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/extras/get-settings/${settingsId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    editSettingsExtras(ctx, postData) {
      console.log(postData)
      return new Promise((resolve, reject) => {
        axios
          .post('/extras/update-settings', postData.baseData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
