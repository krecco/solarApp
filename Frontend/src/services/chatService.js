import axios from '@axios'

export default {
  // ------------------------------------------------
// BACKEND
// ------------------------------------------------

  createContact(payload) {
    return axios.post('/contact/create-contact', payload)
  },

  deleteContactById(id) {
    return axios.delete(`/contact/delete-contact-by-id/${id}`)
  },

  editContactById(id, payload) {
    return axios.put(`/contact/edit-contact/${id}`, payload)
  },

  getContactById(id) {
    return axios.get(`/contact/get-contact/${id}`)
  },

  getContactsForUser(id) {
    return axios.get(`/contact/get-contacts/${id}`)
  },

  getAttachmentById(id) {
    return axios.get(`/message/attachment/${id}`)
  },

  createMessage(formData) {
    return axios.post('/message/create-message', formData)
  },

  deleteMessageById(id) {
    return axios.delete(`/message/delete-message-by-id/${id}`)
  },

  editMessageById(id, payload) {
    return axios.put(`/message/edit-message/${id}`, payload)
  },

  getMessageById(id) {
    return axios.get(`/message/get-message-by-id/${id}`)
  },

  getMessagesForUser(id) {
    return axios.get(`/message/get-messages/${id}`)
  },

  createProfile(payload) {
    return axios.post('/profile/create-profile', payload)
  },

  deleteProfileById(id) {
    return axios.delete(`/profile/delete-profile-by-id/${id}`)
  },

  editProfileById(id, payload) {
    return axios.put(`/profile/edit-profile/${id}`, payload)
  },

  getAuthProfile() {
    return axios.get('/profile/get-auth-profile')
  },

  getProfileById(id) {
    return axios.get(`/profile/get-profile/${id}`)
  },

  getProfiles() {
    return axios.get('/profile/get-profiles')
  },

  addReaction(msgId, contactId, payload) {
    return axios.post(`/message/add-reaction/${msgId}/${contactId}`, payload)
  },

  deleteReactionById(id) {
    return axios.delete(`/message/delete-reaction-by-id/${id}`)
  },
}
