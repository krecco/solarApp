import Vue from 'vue'
import { $apiUrl } from '@serverConfig'

// axios
import axios from 'axios'

const axiosIns = axios.create({
  // You can add your headers here
  // ================================
  baseURL: $apiUrl,
  // timeout: 1000,
  // headers: {'X-Custom-Header': 'foobar'}
})

//  I dont like this! -- auth image plugin !
axios.defaults.headers.common.Authorization = `Bearer ${localStorage.getItem('accessToken')}`

Vue.prototype.$http = axiosIns

export default axiosIns
