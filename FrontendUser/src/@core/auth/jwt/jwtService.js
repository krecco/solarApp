import VueCookies from 'vue-cookies'
import axiosNoJWT from '@/libs/axiosNoJWT'

//  import { initialAbility } from '@/libs/acl/config'
import jwtDefaultConfig from './jwtDefaultConfig'

export default class JwtService {
  // Will be used by this service for making API calls
  axiosIns = null

  // jwtConfig <= Will be used by this service
  jwtConfig = { ...jwtDefaultConfig }

  // For Refreshing Token
  isAlreadyFetchingAccessToken = false

  // For Refreshing Token
  subscribers = []

  constructor(axiosIns, jwtOverrideConfig) {
    this.axiosIns = axiosIns
    this.jwtConfig = { ...this.jwtConfig, ...jwtOverrideConfig }

    // Request Interceptor
    this.axiosIns.interceptors.request.use(
      config => {
        // Get token from localStorage
        const accessToken = this.getToken()

        // If token is present add it to request's Authorization Header
        if (accessToken) {
          // eslint-disable-next-line no-param-reassign
          config.headers.Authorization = `${this.jwtConfig.tokenType} ${accessToken}`
        }
        return config
      },
      error => Promise.reject(error),
    )

    // Add request/response interceptor
    this.axiosIns.interceptors.response.use(
      response => response,
      error => {
        console.log('I am here 1')

        // const { config, response: { status } } = error
        const { config, response } = error
        const originalRequest = config

        // if (status === 401) {
        if (response && response.status === 401) {
          console.log('I am here 2')
          if (!this.isAlreadyFetchingAccessToken) {
            console.log('I am here 3')
            this.isAlreadyFetchingAccessToken = true

            this.refreshToken().then(r => {
              this.isAlreadyFetchingAccessToken = false

              console.log(r)

              if ((r.data.payload.accessToken !== null) && (typeof (r.data.payload.accessToken) !== 'undefined')) {
                console.log('I am here 4')
                // Update accessToken in localStorage
                this.setToken(r.data.payload.accessToken)

                //  not returned by keycloak
                //  this.setRefreshToken(r.data.refreshToken)

                this.onAccessTokenFetched(r.data.payload.accessToken)
              } else {
                console.log('I am here 5 --')
                this.logout()
              }
            })
          }
          const retryOriginalRequest = new Promise(resolve => {
            this.addSubscriber(accessToken => {
              // Make sure to assign accessToken according to your response.
              // Check: https://pixinvent.ticksy.com/ticket/2413870
              // Change Authorization header
              originalRequest.headers.Authorization = `${this.jwtConfig.tokenType} ${accessToken}`
              resolve(this.axiosIns(originalRequest))
            })
          })
          return retryOriginalRequest
        }
        return Promise.reject(error)
      },
    )
  }

  onAccessTokenFetched(accessToken) {
    this.subscribers = this.subscribers.filter(callback => callback(accessToken))
  }

  addSubscriber(callback) {
    this.subscribers.push(callback)
  }

  getToken() {
    return localStorage.getItem(this.jwtConfig.storageTokenKeyName)
  }

  getRefreshToken() {
    return localStorage.getItem(this.jwtConfig.storageRefreshTokenKeyName)
  }

  setToken(value) {
    localStorage.setItem(this.jwtConfig.storageTokenKeyName, value)

    VueCookies.set(this.jwtConfig.storageTokenKeyName, value)
  }

  setRefreshToken(value) {
    localStorage.setItem(this.jwtConfig.storageRefreshTokenKeyName, value)
  }

  login(...args) {
    return this.axiosIns.post(this.jwtConfig.loginEndpoint, ...args)
  }

  logout() {
    console.log('do logut')
    this.axiosIns.post(this.jwtConfig.logoutEndpoint, {
      refreshToken: this.getRefreshToken(),
    })
      .then(() => {
        window.history.go('/login')
        localStorage.removeItem(this.jwtConfig.storageTokenKeyName)
        localStorage.removeItem(this.jwtConfig.storageRefreshTokenKeyName)

        // Remove userData from localStorage
        localStorage.removeItem('userData')

        //  Reset ability
        //  this.$ability.update(initialAbility)
      })
      .catch(() => {
        localStorage.removeItem(this.jwtConfig.storageTokenKeyName)
        localStorage.removeItem(this.jwtConfig.storageRefreshTokenKeyName)

        // Remove userData from localStorage
        localStorage.removeItem('userData')

        //  Reset ability
        //  this.$ability.update(initialAbility)

        //  window.history.go('/login')
        window.location = '/login'
      })

    //  Redirect to login page
    //  router.push({ name: 'login' })
  }

  register(...args) {
    return this.axiosIns.post(this.jwtConfig.registerEndpoint, ...args)
  }

  refreshToken() {
    return axiosNoJWT.post(this.jwtConfig.refreshEndpoint, {
      refreshToken: this.getRefreshToken(),
    })
      .then(response => {
        console.log(response)
        if (response.data.status === 401) {
          localStorage.removeItem(this.jwtConfig.storageTokenKeyName)
          localStorage.removeItem(this.jwtConfig.storageRefreshTokenKeyName)

          // Remove userData from localStorage
          localStorage.removeItem('userData')
          window.location = '/login'
        }
      })
      .catch(() => {
        localStorage.removeItem(this.jwtConfig.storageTokenKeyName)
        localStorage.removeItem(this.jwtConfig.storageRefreshTokenKeyName)

        // Remove userData from localStorage
        localStorage.removeItem('userData')
        window.location = '/login'
      })
  }
}
