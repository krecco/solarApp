<template>
  <component
    :is="tag"
    v-on="$listeners"
  >
    <b-avatar
      size="42"
      :text="getInitials(user.fullname)"
      class="badge-minimal"
      :style="getStyle(user.fullname)"
    />
    <div class="chat-info flex-grow-1">
      <h5 class="mb-0">
        {{ user.fullname }}
      </h5>
      <p
        class="card-text text-truncate"
      >
        {{ user.message }}
      </p>
    </div>
    <div
      class="chat-meta text-nowrap"
    >
      <small class="float-right mb-25 chat-time">{{ formatDateToMonthShort(user.t0, { hour: 'numeric', minute: 'numeric' }) }}</small>
    </div>
    <!--
    <b-avatar
      size="42"
      :text="user.contact.avatar"
      :badge="isChatContact"
      class="badge-minimal"
      :variant="user.contact.avatarBg"
    />
    <div class="chat-info flex-grow-1">
      <h5 class="mb-0">
        {{ user.fullName }}
      </h5>
      <p
        v-if="user.messages[user.messages.length - 1].message !== 'null' && !user.messages[user.messages.length - 1].deleted"
        class="card-text text-truncate"
      >
        {{ isChatContact ? user.messages[user.messages.length - 1].message : '' }}
      </p>
      <p
        v-else-if="user.messages[user.messages.length - 1].deleted"
        class="card-text text-truncate"
      >
        This message has been deleted.
      </p>
      <p
        v-else-if="user.messages[user.messages.length - 1].message === 'null'"
        class="card-text text-truncate"
      >
        Attachment
      </p>
    </div>
    <div
      v-if="isChatContact"
      class="chat-meta text-nowrap"
    >
      <small class="float-right mb-25 chat-time">{{ formatDateToMonthShort(user.messages[user.messages.length - 1].t0, { hour: 'numeric', minute: 'numeric' }) }}</small>
      <b-badge
        v-if="user.unseenMsgs"
        pill
        variant="primary"
        class="float-right margin-5"
      >
        {{ user.unseenMsgs }}
      </b-badge>
    </div>
    -->
  </component>
</template>

<script>
import { BAvatar, BBadge } from 'bootstrap-vue'
import { formatDateToMonthShort } from '@core/utils/filter'
import { getInitials, getStyle } from '@core/utils/avatarUtils'
import useChat from './useChat'

export default {
  components: {
    BAvatar,
    BBadge,
  },
  props: {
    tag: {
      type: String,
      default: 'div',
    },
    user: {
      type: Object,
      required: true,
    },
    isChatContact: {
      type: Boolean,
      default: false,
    },
  },
  /*
  methods: {
    getInitials(string) {
      const names = string.split(' ')
      let initials = names[0].substring(0, 1).toUpperCase()

      if (names.length > 1) {
        initials += names[names.length - 1].substring(0, 1).toUpperCase()
      }
      return initials
    },
    getStyle(str) {
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
  },
  */
  setup() {
    const { resolveAvatarBadgeVariant } = useChat()
    return {
      formatDateToMonthShort,
      resolveAvatarBadgeVariant,
      getStyle,
      getInitials,
    }
  },
}
</script>

<style>

</style>
