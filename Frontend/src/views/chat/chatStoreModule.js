import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchChatsAndContacts() {
      return new Promise((resolve, reject) => {
        axios
          .get('/apps/chat/chats-and-contacts')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getProfileUser() {
      return new Promise((resolve, reject) => {
        axios
          .get('/apps/chat/users/profile-user')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getChat(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/apps/chat/chats/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    sendMessage(ctx, {
      contactId, message, senderId, file,
    }) {
      return new Promise((resolve, reject) => {
        axios
          .post(`/apps/chat/chats/${contactId}`, { message, senderId, file })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    editMessage(ctx, { messageId, message, senderId }) {
      return new Promise((resolve, reject) => {
        axios
          .put(`/apps/chat/chats/${messageId}`, { message, senderId })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    deleteMessage(ctx, { messageId, senderId }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/apps/chat/chats/delete/${messageId}`, { senderId })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    fetchUsers(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/user/chat-list', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchLatestChat() {
      return new Promise((resolve, reject) => {
        axios
          .get('/message/get-latest-chats')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    searchChatUsers(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/message/search-chat/${queryParams.user}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    createContact(ctx, { contactId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/contact/create-contact/${contactId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getMessagesForUser(ctx, { userId, currentChatPage, records }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/message/get-messages/${userId}/${currentChatPage}/${records}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
