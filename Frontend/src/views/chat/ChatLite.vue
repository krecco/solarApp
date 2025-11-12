<template>
  <!-- Need to add height inherit because Vue 2 don't support multiple root ele -->
  <div
    v-if="activeChat.contact"
    style="height: inherit"
  >
    <div
      class="body-content-overlay"
      :class="{'show': shallShowUserProfileSidebar || shallShowActiveChatContactSidebar || mqShallShowLeftSidebar}"
      @click="mqShallShowLeftSidebar=shallShowActiveChatContactSidebar=shallShowUserProfileSidebar=false"
    />

    <!-- Main Area -->
    <section class="chat-app-window">

      <div
        v-if="newConversation"
        class="start-chat-area"
      >
        <div class="mb-1 start-chat-icon">
          <feather-icon
            icon="MessageSquareIcon"
            size="56"
          />
        </div>
        <h4
          class="sidebar-toggle start-chat-text"
          @click="openNewConversation"
        >
          {{ $t('Start Conversation') }}
        </h4>
      </div>

      <!-- Chat Content -->
      <div
        v-else
        class="active-chat"
        style="height: calc( var(--vh, 1vh) * 100 - calc( calc(2rem * 1) + 4.45rem + 3.35rem + 1.3rem + 0rem ) - 65px )"
      >
        <!-- Chat Navbar -->
        <div class="chat-navbar">
          <header class="chat-header">

            <!-- Avatar & Name -->
            <div class="d-flex align-items-center">

              <!-- Toggle Icon -->
              <div class="sidebar-toggle d-block d-lg-none mr-1">
                <feather-icon
                  icon="MenuIcon"
                  class="cursor-pointer"
                  size="21"
                  @click="mqShallShowLeftSidebar = true"
                />
              </div>

              <h6 class="mb-0">
                {{ activeChat.contact.fullName }}
              </h6>
            </div>

            <!-- Contact Actions -->
            <div class="d-flex align-items-center">
              <div class="dropdown">
                <b-dropdown
                  variant="link"
                  no-caret
                  toggle-class="p-0"
                  right
                >
                  <template #button-content>
                    <feather-icon
                      icon="MoreHorizontalIcon"
                      size="17"
                      class="align-middle text-body"
                    />
                  </template>
                  <b-dropdown-item v-b-modal.modal-1>
                    {{ $t('Delete') }}
                  </b-dropdown-item>
                </b-dropdown>
              </div>
            </div>
          </header>
        </div>

        <!-- Delete Modal -->
        <b-modal
          id="modal-1"
          content-class="shadow"
          title="Sure you want to delete this?"
        >
          <p>
            {{ $t('Delete confirmation text') }}
          </p>
          <template #modal-footer="{ ok, cancel }">
            <b-button
              variant="outline-secondary"
              @click="cancel()"
            >
              {{ $t('Cancel') }}
            </b-button>
            <b-button
              variant="primary"
              @click="ok()"
            >
              {{ $t('Yes, delete') }}
            </b-button>
          </template>
        </b-modal>

        <!-- User Chat Area -->
        <vue-perfect-scrollbar
          ref="refChatLogPS"
          :settings="perfectScrollbarSettings"
          class="user-chats scroll-area"
          :style="[chatInputFile ? {'height': 'calc(100% - 65px - 65px - (65px * ' + chatInputFile.length + '))'} : {'height': 'calc(100% - 65px - 65px)'}]"
        >
          <chat-log
            :chat-data="activeChat"
            :profile-user-avatar="profileUserDataMinimal.avatar"
            :profile-user-fullname="profileUserDataMinimal.fullName"
            :profile-user-avatar-bg="profileUserDataMinimal.avatarBg"
            :profile-user-id="profileUserDataMinimal.id"
            @delete-message="deleteMessage"
            @selected-message="selectedMessage"
            @open-chat="openChatOfContact"
            @refresh-chat="refreshChat"
          />
        </vue-perfect-scrollbar>

        <b-form
          v-for="(item, index) in chatInputFile"
          :key="item.name"
          class="chat-app-form"
        >
          <div class="d-flex align-items-center w-100">
            <feather-icon
              v-if="item.type.includes('image')"
              icon="ImageIcon"
              size="24"
              class="text-muted"
            />
            <feather-icon
              v-else
              icon="FileIcon"
              size="24"
              class="text-muted"
            />
            <span class="ml-1">{{ item.name }}</span>
            <feather-icon
              icon="XIcon"
              size="24"
              class="text-muted cursor-pointer ml-auto"
              @click="chatInputFile.splice(index, 1)"
            />
          </div>
        </b-form>

        <!-- Message Input -->
        <b-form
          class="chat-app-form"
          @submit.prevent="submitMessage"
        >
          <b-input-group class="input-group-merge form-send-message mr-1">
            <b-form-input
              v-model="chatInputMessage"
              :placeholder="$t('Enter your message')"
            />
          </b-input-group>
        </b-form>
        <b-form
          class="chat-app-form"
        >
          <div class="position-relative">
            <feather-icon
              icon="SmileIcon"
              size="24"
              class="text-muted mr-1 cursor-pointer"
              @click="openEmojies()"
            />
            <div
              v-if="showEmojies"
              class="card d-flex flex-row position-absolute justify-content-between emoji-bar"
            >
              <div
                class="cursor-pointer emoji-zoom"
                @click="chooseEmojies('👍')"
              >
                👍
              </div>
              <div
                class="cursor-pointer emoji-zoom"
                @click="chooseEmojies('👎');"
              >
                👎
              </div>
              <div
                class="cursor-pointer emoji-zoom"
                @click="chooseEmojies('😍'); "
              >
                😍
              </div>
              <div
                class="cursor-pointer emoji-zoom"
                @click="chooseEmojies('😢');"
              >
                😢
              </div>
              <div
                class="cursor-pointer emoji-zoom"
                @click="chooseEmojies('❤️');"
              >
                ❤️
              </div>
            </div>
          </div>
          <feather-icon
            icon="PaperclipIcon"
            size="24"
            class="text-muted mr-1 cursor-pointer"
            @click="chooseFiles()"
          />
          <input
            id="fileUpload"
            type="file"
            hidden
            multiple
            @change="pickFile($event)"
          >
          <feather-icon
            icon="ImageIcon"
            size="24"
            class="text-muted mr-1 cursor-pointer"
            @click="chooseImages()"
          />
          <input
            id="imageUpload"
            type="file"
            accept="image/*"
            hidden
            multiple
            @change="pickFile($event)"
          >
          <b-button
            v-if="!editFlag"
            class="ml-auto"
            variant="primary"
            @click="sendMessage"
          >
            {{ $t('Send') }}
          </b-button>
          <b-button
            v-else
            class="ml-auto"
            variant="primary"
            @click="editMessage"
          >
            {{ $t('Save') }}
          </b-button>
        </b-form>
      </div>
    </section>

    <!-- Active Chat Contact Details Sidebar -->
    <chat-active-chat-content-details-sidedbar
      :shall-show-active-chat-contact-sidebar.sync="shallShowActiveChatContactSidebar"
      :contact="activeChat.contact || {}"
    />

  </div>
</template>

<script>
import store from '@/store'
import {
  ref, onUnmounted, nextTick,
} from '@vue/composition-api'
import {
  BDropdown, BDropdownItem, BForm, BInputGroup, BFormInput, BButton, BModal,
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
// import { formatDate } from '@core/utils/filter'
import { $themeBreakpoints } from '@themeConfig'
import { useResponsiveAppLeftSidebarVisibility } from '@core/comp-functions/ui/app'
import chatService from '@/services/chatService'
import { useRouter } from '@/@core/utils/utils'
import chatStoreModule from './chatStoreModule'
import ChatActiveChatContentDetailsSidedbar from './ChatActiveChatContentDetailsSidedbar.vue'
import ChatLog from './ChatLog.vue'
import useChat from './useChat'

export default {
  components: {

    // BSV
    BDropdown,
    BDropdownItem,
    BForm,
    BInputGroup,
    BFormInput,
    BButton,
    BModal,

    // 3rd Party
    VuePerfectScrollbar,

    // SFC
    ChatActiveChatContentDetailsSidedbar,
    ChatLog,
  },
  data() {
    return {
      newConversation: false,
      editFlag: false,
      selectedMsg: null,
      url: null,
      dataObjectArray: [],
      showEmojies: false,
    }
  },
  watch: {
    chatsContacts() {
      this.openChatOfContact(this.$route.params.contactId)
    },
  },
  methods: {
    refreshChat() {
      this.fetchChatAndContacts()
      setTimeout(() => {
        this.openChatOfContact(this.activeChat.contactId)
      }, 200)
    },
    openEmojies() {
      if (this.showEmojies === true) {
        this.showEmojies = false
      } else {
        this.showEmojies = true
      }
    },
    chooseEmojies(emoji) {
      this.chatInputMessage += emoji
      this.showEmojies = false
    },
    openNewConversation() {
      this.newConversation = false
    },
    chooseFiles() {
      document.getElementById('fileUpload').click()
    },
    chooseImages() {
      document.getElementById('imageUpload').click()
    },
    pickFile(event) {
      for (let i = 0; i < event.target.files.length; i += 1) {
        this.dataObjectArray[i] = event.target.files[i]
        this.chatInputFile.push(this.dataObjectArray[i])
      }
      document.getElementById('imageUpload').value = ''
    },
    submitMessage() {
      if (!this.editFlag) {
        this.sendMessage()
      } else if (this.editFlag) {
        this.editMessage()
      }
    },
  },
  setup() {
    const CHAT_APP_STORE_MODULE_NAME = 'app-chat'
    const router = useRouter()
    const currentProfileID = router.route.value.params.userId
    // ? Will contain id, name and avatar & status
    const profileUserDataMinimal = ref({})

    // Register module
    if (!store.hasModule(CHAT_APP_STORE_MODULE_NAME)) store.registerModule(CHAT_APP_STORE_MODULE_NAME, chatStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(CHAT_APP_STORE_MODULE_NAME)) store.unregisterModule(CHAT_APP_STORE_MODULE_NAME)
    })

    const { resolveAvatarBadgeVariant } = useChat()

    // Scroll to Bottom ChatLog
    const refChatLogPS = ref(null)
    const scrollToBottomInChatLog = () => {
      const scrollEl = refChatLogPS.value.$el || refChatLogPS.value
      scrollEl.scrollTop = scrollEl.scrollHeight
    }

    // ------------------------------------------------
    // Chats & Contacts
    // ------------------------------------------------
    const chatsContacts = ref([])
    const contacts = ref([])
    const expandedChatsContacts = ref([])

    const fetchChatAndContacts = () => {
      // get User Profile data
      chatService.getProfileById(currentProfileID).then(response => {
        profileUserDataMinimal.value = response.data.payload
      }).finally(() => {
        // get Contacts data for the User
        chatService.getContactsForUser(currentProfileID).then(response => {
          for (let i = 0; i < response.data.payload.length; i += 1) {
            chatService.getProfileById(response.data.payload[i].contactId).then(res => {
              contacts.value[i] = res.data.payload
            })
          }
        }).finally(() => {
          // get Messages for the User
          chatService.getMessagesForUser(currentProfileID).then(response => {
            expandedChatsContacts.value = response.data.payload
            if (response.data.payload.length === 0) {
              chatsContacts.value = expandedChatsContacts.value
            } else {
              for (let i = 0; i < expandedChatsContacts.value.length; i += 1) {
                chatService.getProfileById(expandedChatsContacts.value[i].contactId)
                  .then(res => {
                    expandedChatsContacts.value[i].contact = res.data.payload
                    expandedChatsContacts.value[i].fullName = res.data.payload.fullName
                  }).finally(() => {
                    if (i === expandedChatsContacts.value.length - 1) {
                      chatsContacts.value = expandedChatsContacts.value
                    }
                  })
              }
            }
          })
        })
      })
    }

    fetchChatAndContacts()

    // ------------------------------------------------
    // Single Chat
    // ------------------------------------------------
    const activeChat = ref({})
    const chatInputMessage = ref('')
    const chatInputFile = ref({})
    let newChatFlag = false
    function newChatCreation(contactId) {
      let temporaryChat
      chatService.getProfileById(contactId)
        .then(res => {
          temporaryChat = {
            contact: res.data.payload,
            fullName: res.data.payload.fullName,
            contactId,
            id: chatsContacts.value.length,
            unseenMsgs: 0,
            messages: [],
            userId: profileUserDataMinimal.value.id,
          }
          activeChat.value = temporaryChat
          newChatFlag = true
        })
    }
    function openChatOfContact(userId) {
      // Reset send message input value
      chatInputMessage.value = ''
      chatInputFile.value = []
      let existingChatFlag = false

      if (chatsContacts.value.length === 0) {
        newChatCreation(userId)
        this.newConversation = true
      } else {
        for (let i = 0; i < chatsContacts.value.length; i += 1) {
          if (chatsContacts.value[i].contactId === userId) {
            activeChat.value = chatsContacts.value[i]
            existingChatFlag = true
            // Scroll to bottom
            nextTick(() => { scrollToBottomInChatLog() })
          }
        }
        if (!existingChatFlag) {
          newChatCreation(userId)
          this.newConversation = true
        }
      }
      activeChat.value.unseenMsgs = 0
      // if SM device =>  Close Chat & Contacts left sidebar
      // eslint-disable-next-line no-use-before-define
      mqShallShowLeftSidebar.value = false
    }

    function sendMessageDispatch(formData, hasFile) {
      chatService.createMessage(formData)
        .then(response => {
          const newMessageData = response.data.payload

          // Add message to log
          activeChat.value.messages.push(newMessageData)

          // Reset send message input value
          chatInputMessage.value = ''

          if (newChatFlag === true) {
            fetchChatAndContacts()
            newChatFlag = false
          }

          if (hasFile === true) {
            chatInputFile.value = []
          }

          // Set Last Message for active contact
          // const contact = chatsContacts.value.find(c => c.id === activeChat.value.contactId)
          // contact.chat.lastMessage = newMessageData

          // Scroll to bottom
          nextTick(() => { scrollToBottomInChatLog() })
        })
    }

    const sendMessage = () => {
      if (!chatInputMessage.value && chatInputFile.value.length === 0) return
      if (chatInputFile.value.length !== 0) {
        if (chatInputMessage.value) {
          const formData = new FormData()
          formData.append('message', chatInputMessage.value)
          formData.append('recipientId', activeChat.value.contact.id)
          formData.append('senderId', profileUserDataMinimal.value.id)
          sendMessageDispatch(formData, false)
        }
        for (let i = 0; i < chatInputFile.value.length; i += 1) {
          const formData = new FormData()
          formData.append('message', null)
          formData.append('recipientId', activeChat.value.contact.id)
          formData.append('senderId', profileUserDataMinimal.value.id)
          formData.append('file', chatInputFile.value[i])
          sendMessageDispatch(formData, true)
        }
      } else {
        const formData = new FormData()
        formData.append('message', chatInputMessage.value)
        formData.append('recipientId', activeChat.value.contact.id)
        formData.append('senderId', profileUserDataMinimal.value.id)
        sendMessageDispatch(formData, false)
      }
    }

    function selectedMessage(message) {
      this.selectedMsg = message
      this.editFlag = true
      chatInputMessage.value = this.selectedMsg.msg
    }

    function deleteMessage(data) {
      chatService.deleteMessageById(data.msgId).then(response => {
        if (response.status === 200) {
          for (let i = 0; i < activeChat.value.messages.length; i += 1) {
            if (activeChat.value.messages[i].id === data.msgId) {
              activeChat.value.messages[i].deleted = true
            }
          }
        }
      })
    }

    function editMessage() {
      if (!chatInputMessage.value) return
      if (chatInputMessage.value === this.selectedMsg.msg) return
      const payload = {
        id: this.selectedMsg.msgId,
        t0: this.selectedMsg.time,
        message: chatInputMessage.value,
        senderId: this.selectedMsg.senderId,
        recipientId: this.selectedMsg.recipientId,
        read: this.selectedMsg.read,
        smtpDelivered: this.selectedMsg.delivered,
        edited: true,
        deleted: this.selectedMsg.deleted,
        links: this.selectedMsg.links,
        file: this.selectedMsg.file,
      }
      chatService.editMessageById(this.selectedMsg.msgId, payload)
        .then(response => {
          if (response.status === 200) {
            chatInputMessage.value = ''
            this.editFlag = false
            for (let i = 0; i < activeChat.value.messages.length; i += 1) {
              if (activeChat.value.messages[i].id === this.selectedMsg.msgId) {
                activeChat.value.messages[i].message = payload.message
                activeChat.value.messages[i].edited = payload.edited
              }
            }
          }
        })
    }

    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    // User Profile Sidebar
    // ? Will contain all details of profile user (e.g. settings, about etc.)
    const profileUserData = ref({})

    const shallShowUserProfileSidebar = ref(false)
    const showUserProfileSidebar = () => {
      // get User Profile data
      chatService.getProfileById(currentProfileID).then(response => {
        profileUserData.value = response.data.payload
        shallShowUserProfileSidebar.value = true
      })
    }

    // Active Chat Contact Details
    const shallShowActiveChatContactSidebar = ref(false)

    // UI + SM Devices
    // Left Sidebar Responsiveness
    const { mqShallShowLeftSidebar } = useResponsiveAppLeftSidebarVisibility()
    const startConversation = () => {
      if (store.state.app.windowWidth < $themeBreakpoints.lg) {
        mqShallShowLeftSidebar.value = true
      }
    }

    return {
      // Filters
      // formatDate,

      // useChat
      resolveAvatarBadgeVariant,

      // Chat & Contacts
      chatsContacts,
      contacts,

      // Single Chat
      refChatLogPS,
      activeChat,
      chatInputMessage,
      chatInputFile,
      openChatOfContact,
      fetchChatAndContacts,
      sendMessage,
      editMessage,
      selectedMessage,
      deleteMessage,

      // Profile User Minimal Data
      profileUserDataMinimal,

      // User Profile Sidebar
      profileUserData,
      shallShowUserProfileSidebar,
      showUserProfileSidebar,

      // Active Chat Contact Details
      shallShowActiveChatContactSidebar,

      // UI
      perfectScrollbarSettings,

      // UI + SM Devices
      startConversation,
      mqShallShowLeftSidebar,
    }
  },
}
</script>

<style>
  .content-right {
    width: 100% !important;
  }
  .preview-image{
    max-height: 40px;
    max-width: 40px;
  }
  .emoji-bar{
    top: -2.9rem;
    margin: 0 !important;
    left: -0.1rem;
    min-width: 155px !important;
    padding: 0.7rem 1rem;
  }
  .emoji-zoom{
    transition: transform .2s;
  }
  .emoji-zoom:hover {
    transform: scale(1.5);
  }
</style>

<style lang="scss">
@import "~@core/scss/base/pages/app-chat.scss";
@import "~@core/scss/base/pages/app-chat-list.scss";
</style>
