<template>
  <!-- Need to add height inherit because Vue 2 don't support multiple root ele -->
  <div style="height: inherit">
    <div
      class="body-content-overlay"
      :class="{'show': shallShowUserProfileSidebar || shallShowActiveChatContactSidebar || mqShallShowLeftSidebar}"
      @click="mqShallShowLeftSidebar=shallShowActiveChatContactSidebar=shallShowUserProfileSidebar=false"
    />

    <!-- Main Area -->
    <section class="chat-app-window">

      <!-- Start Chat Logo -->
      <div
        v-if="!activeChat.contact && !newConversation"
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

      <!-- Start New Conv -->
      <div
        v-else-if="newConversation"
        class="h-100"
      >
        <div class="active-chat">
          <!-- Chat Navbar -->
          <div class="chat-navbar">
            <header class="chat-header">

              <!-- Avatar & Name -->
              <div class="d-flex align-items-center justify-content-center w-100">

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
                  {{ $t('New Conversation') }}
                </h6>
              </div>
            </header>
          </div>

          <div class="chat-navbar">
            <header class="chat-header w-100">

              <!-- Avatar & Name -->
              <div class="d-flex align-items-center w-100">

                <!-- Toggle Icon -->
                <div class="sidebar-toggle d-block d-lg-none mr-1">
                  <feather-icon
                    icon="MenuIcon"
                    class="cursor-pointer"
                    size="21"
                    @click="mqShallShowLeftSidebar = true"
                  />
                </div>
                <div class="w-100">
                  <v-select
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    label="fullName"
                    :placeholder="$t('Type a name or select one, to start a new conversation')"
                    :options="contacts"
                    @input="selectConversation($event)"
                    @search="fetchUsers($event)"
                  >
                    <template v-slot:option="option">
                      <b-avatar
                        size="30"
                        class="avatar-border-2 box-shadow-1 mr-1"
                        :style="getStyle(option.fullName)"
                        :text="option.avatar"
                      />
                      {{ option.fullName }}
                    </template>
                  </v-select>
                </div>
              </div>
            </header>
          </div>

          <!-- User Chat Area -->
          <vue-perfect-scrollbar
            ref="refChatLogPS"
            :settings="perfectScrollbarSettings"
            class="user-chats scroll-area"
            style="height: calc(100% - 65px - 65px - 65px - 65px)"
          />

          <!-- Message Input -->

          <b-form
            class="chat-app-form"
            @submit.prevent="sendMessage"
          >
            <b-input-group class="input-group-merge form-send-message mr-1">
              <b-form-input
                v-model="chatInputMessage"
                :placeholder="$t('Enter your message')"
                disabled
              />
            </b-input-group>
          </b-form>
          <b-form
            class="chat-app-form"
          >
            <feather-icon
              icon="SmileIcon"
              size="24"
              class="text-muted mr-1 cursor-pointer"
            />
            <feather-icon
              icon="PaperclipIcon"
              size="24"
              class="text-muted mr-1 cursor-pointer"
            />
            <input
              id="fileUpload"
              type="file"
              hidden
            >
            <feather-icon
              icon="ImageIcon"
              size="24"
              class="text-muted mr-1 cursor-pointer"
            />
            <input
              id="imageUpload"
              type="file"
              accept="image/*"
              hidden
            >
            <b-button
              class="ml-auto"
              variant="primary"
              disabled
              @click="sendMessage"
            >
              {{ $t('Send') }}
            </b-button>
          </b-form>
        </div>
      </div>

      <!-- Chat Content -->
      <div
        v-else
        class="active-chat"
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
                {{ activeChatProfile.lastName }} {{ activeChatProfile.firstName }}
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
              {{ $t('Yes delete') }}
            </b-button>
          </template>
        </b-modal>

        <!-- User Chat Area -->
        <vue-perfect-scrollbar
          ref="refChatLogPS"
          :settings="perfectScrollbarSettings"
          class="user-chats scroll-area"
          :style="[chatInputFile ? {'height': 'calc(100% - 65px - 65px - 65px - (65px * ' + chatInputFile.length + '))'} : {'height': 'calc(100% - 65px - 65px - 65px)'}]"
        >
          <chat-log
            :chat-data="activeChat"
            :active-chat-messages="activeChatMessages"
            :profile-user-avatar="profileUserDataMinimal.avatar"
            :profile-user-fullname="profileUserDataMinimal.fullName"
            :profile-user-avatar-bg="profileUserDataMinimal.avatarBg"
            :profile-user-id="profileUserDataMinimal.id"
            @selected-message="selectedMessage"
            @delete-message="deleteMessage"
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

    <!-- Sidebar -->
    <portal to="content-renderer-sidebar-left">
      <chat-left-sidebar
        :chats-contacts="chatsContacts"
        :contacts="contacts"
        :active-chat-contact-id="activeChat.contact ? activeChat.contact.id : null"
        :shall-show-user-profile-sidebar.sync="shallShowUserProfileSidebar"
        :profile-user-data="profileUserData"
        :profile-user-minimal-data="profileUserDataMinimal"
        :mq-shall-show-left-sidebar.sync="mqShallShowLeftSidebar"
        @open-new-conversation="openNewConversation"
        @close-new-conversation="closeNewConversation"
        @show-user-profile="showUserProfileSidebar"
        @open-chat="openChatOfContact"
      />
    </portal>
  </div>
</template>

<script>
import store from '@/store'
import {
  ref, onUnmounted, nextTick,
} from '@vue/composition-api'
import {
  BDropdown, BDropdownItem, BForm, BInputGroup, BFormInput, BButton, BAvatar, BModal,
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
// import { formatDate } from '@core/utils/filter'
import { $themeBreakpoints } from '@themeConfig'
import { useResponsiveAppLeftSidebarVisibility } from '@core/comp-functions/ui/app'
import vSelect from 'vue-select'
import { getStyle } from '@core/utils/avatarUtils'
import chatService from '@/services/chatService'
//  import { useRouter } from '@/@core/utils/utils'
import ChatLeftSidebar from './ChatLeftSidebar.vue'
import chatStoreModule from './chatStoreModule'
import ChatActiveChatContentDetailsSidedbar from './ChatActiveChatContentDetailsSidedbar.vue'
import ChatLog from './ChatLog.vue'
import useChat from './useChat'
// import ContactsLite from './ContactsLite.vue'

export default {
  components: {

    // BSV
    BDropdown,
    BDropdownItem,
    BForm,
    BInputGroup,
    BFormInput,
    BButton,
    BAvatar,
    BModal,
    vSelect,

    // 3rd Party
    VuePerfectScrollbar,

    // SFC
    ChatLeftSidebar,
    ChatActiveChatContentDetailsSidedbar,
    ChatLog,
    // ContactsLite,
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
  mounted() {
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
      this.newConversation = true
    },
    closeNewConversation() {
      this.newConversation = false
    },
    selectConversation(event) {
      this.openChatOfContact(event.id)
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
    /*
    getStyle(str) {
      console.log(str)
      let hash = 0
      if (str.length === 0) return hash
      for (let i = 0; i < str.length; i += 1) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash) //  eslint-disable-line no-bitwise
        hash &= hash //  eslint-disable-line no-bitwise
      }
      let color = '#'
      let value = 0
      for (let i = 0; i < 3; i += 1) {
        value = (hash >> (i * 8)) & 255 //  eslint-disable-line no-bitwise
        color += (`00${value.toString(16)}`).substr(-2)
      }
      return `background-color:${color}`
    },
    */
  },
  setup() {
    const CHAT_APP_STORE_MODULE_NAME = 'app-chat'

    console.log('this refs')
    console.log(this)

    /*
    const router = useRouter()
    const currentProfileID = router.route.value.params.userId
    */
    /*
    console.log('userview')
    console.log(JSON.parse(localStorage.getItem('userData')).id)
    */

    const currentProfileID = JSON.parse(localStorage.getItem('userData')).id

    // ? Will contain id, name and avatar & status
    const profileUserDataMinimal = ref({})
    const searchQuery = ref('')

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
    //  const expandedChatsContacts = ref([])
    const activeChatMessages = ref([])
    const activeChat = ref({})
    const activeChatProfile = ref({})

    //  !!!!------ this is really bad and should be reconstructed ----!!!!!
    const fetchChatAndContacts = () => {
      // get User Profile data
      chatService.getAuthProfile().then(response => {
        //  profileUserDataMinimal.value = response.data.payload
        profileUserDataMinimal.value = response.data
      })
      /*
      .finally(() => {
        // get Contacts data for the User
        chatService.getContactsForUser(currentProfileID).then(response => {
          // just to skip error for now
          console.log(response.data.payload)
          for (let i = 0; i < response.data.payload.length; i += 1) {
            chatService.getProfileById(response.data.payload[i].contactId).then(res => {
              contacts.value[i] = res.data.payload
            })
          }
        }) */
        .finally(() => {
          // get Messages for the User
          /*
          chatService.getMessagesForUser(currentProfileID).then(response => {
            expandedChatsContacts.value = response.data.payload
            if (response.data.payload.length === 0) {
              chatsContacts.value = expandedChatsContacts.value
            } else {
              for (let i = 0; i < expandedChatsContacts.value.length; i += 1) {
                chatService.getProfileById(expandedChatsContacts.value[i].contactId)
                  .then(res => {
                    expandedChatsContacts.value[i].contact = res.data.payload
                    //  tmp expandedChatsContacts.value[i].fullName = res.data.payload.fullName
                    expandedChatsContacts.value[i].fullName = 'joza'
                  }).finally(() => {
                    if (i === expandedChatsContacts.value.length - 1) {
                      //  chatsContacts.value = expandedChatsContacts.value
                    }
                  })
              }
            }
          })
          */
        })
      //  })
    }

    fetchChatAndContacts()

    const fetchUsers = e => {
      // there in the chat module is unload ... need to find it!
      if (!store.hasModule(CHAT_APP_STORE_MODULE_NAME)) store.registerModule(CHAT_APP_STORE_MODULE_NAME, chatStoreModule)
      console.log(e)
      console.log(searchQuery.value)
      store
        .dispatch(`${CHAT_APP_STORE_MODULE_NAME}/fetchUsers`, {
          q: e,
          perPage: 20,
          customerType: -1,
          page: 1,
          sortBy: 'lastName',
          sortDesc: false,
        })
        .then(response => {
          console.log(response)
          contacts.value = response.data
        })
        .catch(() => {
          console.log('zis is an error')
        })
    }

    //  fetchUsers()

    const fetchLatestChat = () => {
      console.log('fetching latest chat')
      store
        .dispatch(`${CHAT_APP_STORE_MODULE_NAME}/fetchLatestChat`)
        .then(response => {
          console.log(response)
          chatsContacts.value = response.data
        })
        .catch(() => {
          console.log('zis is an error')
        })
    }

    fetchLatestChat()

    /*
    const getMessagesForUser = userId => {
      if (!store.hasModule(CHAT_APP_STORE_MODULE_NAME)) store.registerModule(CHAT_APP_STORE_MODULE_NAME, chatStoreModule)
      store
        .dispatch(`${CHAT_APP_STORE_MODULE_NAME}/getMessagesForUser`, { userId })
        .then(response => {
          activeChatMessages.value = response.data
          activeChat.value.messages = response.data
        })
        .catch(() => {
          console.log('zis is an error')
        })
    }
    */

    // ------------------------------------------------
    // Single Chat
    // ------------------------------------------------
    const chatInputMessage = ref('')
    const chatInputFile = ref({})
    let newChatFlag = false
    function newChatCreation(contactId) {
      let temporaryChat
      store
        .dispatch(`${CHAT_APP_STORE_MODULE_NAME}/createContact`, { contactId })
        .then(response => {
          console.log(response)
          chatService.getProfileById(contactId)
            .then(res => {
              console.log('i ar here')
              console.log(res.data)

              //   WTF, what about db?!?!?!?!?!?!
              temporaryChat = {
                contact: res.data,
                fullName: res.data.firsName,
                contactId,
                id: chatsContacts.value.length,
                unseenMsgs: 0,
                messages: [],
                //  userId: profileUserDataMinimal.value.id,
                userId: res.data.id,
              }
              activeChat.value = temporaryChat

              //  activeChat.profile.value = res.data

              activeChatProfile.value = res.data

              newChatFlag = true
            })
        })
        .catch(() => {
          console.log('zis is an error')
        })
    }
    function openChatOfContact(userId) {
      console.log('get chat messages')
      console.log('openChatOfContact -- ')

      console.log('check db for latest messages')
      console.log(userId)

      //  getMessagesForUser(userId)

      store
        .dispatch(`${CHAT_APP_STORE_MODULE_NAME}/getMessagesForUser`, { userId })
        .then(response => {
          if (response.data.length === 0) {
            console.log('++++newChatCreation++++')
            newChatCreation(userId)
          }
          newChatCreation(userId)

          activeChatMessages.value = response.data
          //  activeChat.value.messages = response.data
        })
        .catch(() => {
          console.log('zis is an error')
        })

      // Reset send message input value
      chatInputMessage.value = ''
      chatInputFile.value = []
      let existingChatFlag = false

      console.log(chatsContacts)
      console.log(userId)

      /* bkp
      if (chatsContacts.value.length === 0) {
        console.log('openChatOfContact -- 1')
        newChatCreation(userId)
      } else {
        console.log('openChatOfContact -- 2')
        for (let i = 0; i < chatsContacts.value.length; i += 1) {
          if (chatsContacts.value[i].contactId === userId) {
            console.log('openChatOfContact -- 3')
            activeChat.value = chatsContacts.value[i]
            existingChatFlag = true
            // Scroll to bottom
            nextTick(() => { scrollToBottomInChatLog() })
          }
        }
        if (!existingChatFlag) {
          console.log('openChatOfContact -- 4')
          newChatCreation(userId)
        }
      }
      */

      if (chatsContacts.value.length === 0) {
        console.log('openChatOfContact -- 1')
        //  newChatCreation(userId)
      } else {
        console.log('openChatOfContact -- 2')
        for (let i = 0; i < chatsContacts.value.length; i += 1) {
          if (chatsContacts.value[i].contactId === userId) {
            console.log('openChatOfContact -- 3')
            activeChat.value = chatsContacts.value[i]
            existingChatFlag = true
            // Scroll to bottom
            nextTick(() => { scrollToBottomInChatLog() })
          }
        }
        if (!existingChatFlag) {
          console.log('openChatOfContact -- 4')
          //  newChatCreation(userId)
        }
      }

      console.log('openChatOfContact -- 5')
      activeChat.value.unseenMsgs = 0
      // if SM device =>  Close Chat & Contacts left sidebar
      // eslint-disable-next-line no-use-before-define
      mqShallShowLeftSidebar.value = false
    }

    function sendMessageDispatch(formData, hasFile) {
      chatService.createMessage(formData)
        .then(response => {
          const newMessageData = response.data.payload

          console.log('msg response')
          console.log(newMessageData)

          // Add message to log
          activeChat.value.messages.push(newMessageData)

          activeChatMessages.value.push(newMessageData)

          // Reset send message input value
          chatInputMessage.value = ''

          if (newChatFlag === true) {
            //  fetchChatAndContacts()
            fetchLatestChat()
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
      activeChatMessages,
      activeChatProfile,

      fetchUsers,

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
      getStyle,

      // UI + SM Devices
      startConversation,
      mqShallShowLeftSidebar,
    }
  },
}
</script>

<style lang="scss" scoped>
  .style-chooser::v-deep .vs__dropdown-toggle {
    border:none
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
@import '@core/scss/vue/libs/vue-select.scss';
</style>
