<template>
  <div class="chats">
    <!--
    <div
      v-for="chat in activeChatMessages"
      :key="chat.id"
    >
      {{ chat.senderId }}
      |
      {{ chat.t0 }}
      {{ chat.message }}
    </div>
    -->
    <div
      v-for="(msgGrp, index) in formattedChatData.formattedChatLog"
      :key="msgGrp.senderId+String(index)"
      class="chat-left"
      :class="{'chat-left': msgGrp.senderId === formattedChatData.contact.id}"
    >
      <template v-if="formattedChatData.formattedChatLog[index-1]">
        <div
          v-if="formatDateToMonthShort(msgGrp.messages[0]['time'], { hour: 'numeric', minute: 'numeric' })
            !== formatDateToMonthShort(formattedChatData.formattedChatLog[index-1].messages[0].time, { hour: 'numeric', minute: 'numeric' })"
          class="d-flex align-items-center w-100"
        >
          <hr class="flex-grow-1 mr-1">
          <span v-if="new Date().setHours(0, 0, 0, 0) == new Date(msgGrp.messages[0].time).setHours(0,0,0,0)">{{ $t('Today') }}</span>
          <span v-else>{{ formatDateToMonthShort(msgGrp.messages[0].time, { hour: 'numeric', minute: 'numeric' }) }}</span>
          <hr class="flex-grow-1 ml-1">
        </div>
      </template>
      <div v-else>
        <div class="d-flex align-items-center w-100">
          <hr class="flex-grow-1 mr-1">
          <span v-if="new Date().setHours(0, 0, 0, 0) == new Date(msgGrp.messages[0].time).setHours(0,0,0,0)">{{ $t('Today') }}</span>
          <span v-else>{{ formatDateToMonthShort(msgGrp.messages[0].time, { hour: 'numeric', minute: 'numeric' }) }}</span>
          <hr class="flex-grow-1 ml-1">
        </div>
      </div>
      <div class="chat-avatar d-flex align-items-center">
        <b-avatar
          size="36"
          class="avatar-border-2 box-shadow-1 mb-1"
          :variant="msgGrp.senderId === formattedChatData.contact.id ? formattedChatData.contact.avatarBg : profileUserAvatarBg"
          :text="msgGrp.senderId === formattedChatData.contact.id ? formattedChatData.contact.avatar : profileUserAvatar"
        />
        <span
          v-if="msgGrp.senderId == formattedChatData.contact.id"
          class="ml-1 mb-1"
        >
          {{ msgGrp.senderId === formattedChatData.contact.id ? formattedChatData.contact.fullName : contactFullName }} - {{ msgGrp.messages[0].time | formatTime }}</span>
        <span
          v-else
          class="ml-1 mb-1"
        >{{ profileUserFullname }} - {{ msgGrp.messages[0].time | formatTime }}</span>
      </div>
      <div
        v-for="(msgData) in msgGrp.messages"
        :key="msgData.msgId"
        class="chat-body w-100 d-flex justify-content-between position-relative"
        @mouseenter="showReactionBar(msgData)"
        @mouseleave="hideReactionBar()"
      >
        <div
          v-if="msgData.links.length > 0 && !msgData.deleted"
          class="chat-content"
        >
          <img
            v-if="msgData.links[0].type === 'png' || msgData.links[0].type === 'jpg' || msgData.links[0].type === 'jpeg' || msgData.links[0].type === 'gif' || msgData.links[0].type === 'bmp' || msgData.links[0].type === 'tiff' || msgData.links[0].type === 'svg'"
            class="uploaded-image cursor-pointer"
            :src="msgData.links[0].url"
            :alt="msgData.links[0].name"
            @click="enlargeImage(msgData.links[0])"
          >
          <div
            v-else
            class="d-flex cursor-pointer align-items-center"
            @click="downloadFile(msgData.links[0])"
          >
            <feather-icon
              icon="FileIcon"
              size="24"
              class="text-muted"
            />
            <p style="margin-left: 4px">
              {{ msgData.links[0].name }}
            </p>
          </div>
        </div>
        <div
          v-else
          class="chat-content d-flex position-relative"
          @mouseenter="showEmojiBar(msgData)"
          @mouseleave="hideEmojiBar()"
        >
          <p v-if="!msgData.deleted">
            {{ msgData.msg }}
          </p>
          <p
            v-if="msgData.edited && !msgData.deleted"
            class="info-text"
          >
            {{ $t('(Edited)') }}
          </p>
          <p
            v-if="msgData.deleted"
            class="info-text"
          >
            {{ $t('This message has been deleted.') }}
          </p>
          <div
            v-if="selectedEmojiIndex == msgData.msgId && !msgData.deleted"
            class="chat-content d-flex position-absolute justify-content-between emoji-bar"
          >
            <div
              class="cursor-pointer emoji-zoom"
              @click="addReaction(msgData, profileUserId, '👍')"
            >
              👍
            </div>
            <div
              class="cursor-pointer emoji-zoom"
              @click="addReaction(msgData, profileUserId, '👎')"
            >
              👎
            </div>
            <div
              class="cursor-pointer emoji-zoom"
              @click="addReaction(msgData, profileUserId, '😍')"
            >
              😍
            </div>
            <div
              class="cursor-pointer emoji-zoom"
              @click="addReaction(msgData, profileUserId, '😢')"
            >
              😢
            </div>
            <div
              class="cursor-pointer emoji-zoom"
              @click="addReaction(msgData, profileUserId, '❤️')"
            >
              ❤️
            </div>
          </div>
          <div class="reaction-bar d-flex w-100">
            <div
              v-for="(msgReact) in msgData.reactions"
              :key="msgReact.id"
              class="reactions d-flex cursor-pointer"
              @click="removeReaction(msgReact)"
            >
              <span>{{ msgReact.value }}</span>
            </div>
          </div>
        </div>
        <div v-if="selectedIndex == msgData.msgId && !msgData.deleted">
          <b-dropdown
            id="dropdown-dropup"
            dropup
            right
            size="lg"
            variant="link"
            toggle-class="text-decoration-none"
            no-caret
          >
            <template #button-content>
              <feather-icon
                icon="MoreHorizontalIcon"
                size="20"
                class="text-muted cursor-pointer"
              />
            </template>
            <b-dropdown-item
              v-if="msgData.links.length === 0"
              @click="$emit('selected-message', selected_data)"
            >
              {{ $t('Edit') }}
            </b-dropdown-item>
            <b-dropdown-item @click="$emit('delete-message', selected_data)">
              {{ $t('Delete') }}
            </b-dropdown-item>
          </b-dropdown>
        </div>
      </div>
    </div>
    <b-modal
      v-if="selectedImage"
      id="modal-center"
      centered
      hide-footer
      hide-header
    >
      <img
        class="h-100 w-100"
        :src="selectedImage.url"
        :alt="selectedImage.name"
      >
    </b-modal>
  </div>
</template>

<script>
import { computed } from '@vue/composition-api'
import { BAvatar, BDropdown, BDropdownItem } from 'bootstrap-vue'
import { formatDateToMonthShort } from '@core/utils/filter'
import chatService from '@/services/chatService'

export default {
  components: {
    BAvatar,
    BDropdown,
    BDropdownItem,
  },
  props: {
    activeChatMessages: {
      type: Array,
      required: true,
    },
    activeChatUser: {
      type: Object,
      required: true,
    },
    chatData: {
      type: Object,
      required: true,
    },
    profileUserAvatar: {
      type: String,
      required: true,
    },
    profileUserAvatarBg: {
      type: String,
      required: true,
    },
    contactFullName: {
      type: String,
      required: false,
      default: '',
    },
    profileUserFullname: {
      type: String,
      required: true,
    },
    profileUserId: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      currentDate: new Date(),
      selected_data: null,
      selectedIndex: null,
      selectedEmojiIndex: null,
      selectedImage: null,
    }
  },
  watch: {
    chatData() {
      console.log('works from chat data')
    },
  },
  methods: {
    showReactionBar(data) {
      if (this.daysBetween(data.time, this.currentDate) < 1) {
        this.selected_data = data
        if (this.selectedIndex !== data.msgId) {
          this.selectedIndex = data.msgId
        } else {
          this.selectedIndex = null
        }
      }
    },

    hideReactionBar() {
      this.selected_data = null
      this.selectedIndex = null
    },

    addReaction(data, userId, val) {
      const payload = {
        contactId: userId,
        value: val,
      }
      let duplicateFlag = false
      if (data.reactions.length > 0) {
        for (let i = 0; i < data.reactions.length; i += 1) {
          if (data.reactions[i].contactId === userId && data.reactions[i].value === payload.value) {
            this.removeReaction(data.reactions[i])
            duplicateFlag = true
          }
        }
        if (!duplicateFlag) {
          chatService.addReaction(data.msgId, userId, payload).then(() => {
            this.$emit('refresh-chat')
          })
        }
      } else {
        chatService.addReaction(data.msgId, userId, payload).then(() => {
          this.$emit('refresh-chat')
        })
      }
    },

    removeReaction(data) {
      if (data.contactId === this.profileUserId) {
        chatService.deleteReactionById(data.id).then(() => {
          this.$emit('refresh-chat')
        })
      }
    },

    showEmojiBar(mData) {
      if (this.selectedEmojiIndex !== mData.msgId) {
        this.selectedEmojiIndex = mData.msgId
      } else {
        this.selectedEmojiIndex = null
      }
    },

    hideEmojiBar() {
      this.selectedEmojiIndex = null
    },

    enlargeImage(data) {
      this.selectedImage = data
      this.$bvModal.show('modal-center')
    },

    downloadFile(data) {
      const link = document.createElement('a')
      link.href = data.url
      link.download = data.name

      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    },

    treatAsUTC(date) {
      const result = new Date(date)
      result.setMinutes(result.getMinutes() - result.getTimezoneOffset())
      return result
    },

    daysBetween(startDate, endDate) {
      const hoursPerDay = 60 * 60 * 1000
      return (this.treatAsUTC(endDate) - this.treatAsUTC(startDate)) / hoursPerDay
    },
  },
  setup(props) {
    const formattedChatData = computed(() => {
      const contact = {
        id: props.activeChatUser.id,
        avatar: props.activeChatUser.avatar,
        avatarBg: props.activeChatUser.avatarBg,
        fullName: props.activeChatUser.fullName,
      }

      let chatLog = []
      if (props.chatData.messages) {
        chatLog = props.chatData.messages
      }

      chatLog = props.activeChatMessages

      const formattedChatLog = []
      let chatMessageSenderId = chatLog[0] ? chatLog[0].senderId : undefined
      let msgGroup = {
        sender: chatMessageSenderId,
        messages: [],
      }

      chatLog.forEach((msg, index) => {
        console.log(msg)
        console.log(chatMessageSenderId)
        if (chatMessageSenderId === msg.senderId) {
          console.log('aaa')
          msgGroup.messages.push({
            msgId: msg.id,
            msg: msg.message,
            time: msg.t0,
            senderId: msg.senderId,
            recipientId: msg.recipientId,
            read: msg.read,
            delivered: msg.smtpDelivered,
            reactions: msg.messageReactions,
            links: msg.links,
            edited: msg.edited,
            deleted: msg.deleted,
          })
        } else {
          console.log('bbb')
          chatMessageSenderId = msg.senderId
          formattedChatLog.push(msgGroup)
          msgGroup = {
            senderId: msg.senderId,
            messages: [{
              msgId: msg.id,
              msg: msg.message,
              time: msg.t0,
              senderId: msg.senderId,
              recipientId: msg.recipientId,
              read: msg.read,
              delivered: msg.smtpDelivered,
              reactions: msg.messageReactions,
              links: msg.links,
              edited: msg.edited,
              deleted: msg.deleted,
            }],
          }
        }
        if (index === chatLog.length - 1) formattedChatLog.push(msgGroup)
      })

      return {
        formattedChatLog,
        contact,
        profileUserAvatar: props.profileUserAvatar,
        profileUserAvatarBg: props.profileUserAvatarBg,
        profileUserFullname: props.profileUserFullname,
        profileUserId: props.profileUserId,
        contactFullName: props.contactFullName,
      }
    })

    return {
      formattedChatData,
      formatDateToMonthShort,
    }
  },
}
</script>

<style>
  .chat-body {
    overflow: unset !important;
  }
  .show > .dropdown-menu {
    top: 8px !important;
  }
  .uploaded-image {
    max-height: 190px;
    max-width: 100%;
    margin: auto;
  }
  .info-text {
    color: #777777 !important;
    margin-left: 4px !important;
  }
  .emoji-bar {
    top: -2.9rem;
    margin: 0 !important;
    left: -0.1rem;
    min-width: 155px !important;
    border: 1px solid black;
  }
  .emoji-zoom {
    transition: transform .2s;
  }
  .emoji-zoom:hover {
    transform: scale(1.5);
  }
  .reaction-bar {
    position: absolute;
    bottom: -1rem;
    left: 0.2rem;
  }
  .reactions {
    background: #bde0ff;
    padding: 0 0.5rem;
    margin-right: 3px;
    border: 1px solid #0088ff;
    color: #0088ff;
    border-radius: 9px;
    font-size: 12px;
  }
</style>
