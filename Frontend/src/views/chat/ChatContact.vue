<template>
  <component
    :is="tag"
    v-on="$listeners"
  >
    <b-avatar
      size="42"
      :text="getInitials(user.fullname)"
      :badge="isUnread(user)"
      badge-variant="danger"
      badge-top
      class="badge-minimal"
      :style="getStyle(user.fullname)"
    />
    <div class="chat-info flex-grow-1">
      <h5 class="mb-0">
        {{ user.fullname }}
      </h5>
      <p
        v-if="user.message !== 'null'"
        class="card-text text-truncate"
      >
        {{ user.message }}
      </p>
      <p
        v-if="user.message === 'null'"
        class="card-text text-truncate"
      >
        Datei
      </p>
    </div>
    <div
      class="chat-meta text-nowrap"
    >
      <!--<small class="float-right mb-25 chat-time">{{ formatDateToMonthShort(user.t0, { hour: 'numeric', minute: 'numeric' }) }}</small>-->
      <small class="float-right mb-25 chat-time">{{ user.t0 | moment("MMM D") }}</small>
    </div>
  </component>
</template>

<script>
import { BAvatar, BBadge } from 'bootstrap-vue'
//  import { formatDateToMonthShort } from '@core/utils/filter'
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
  data() {
    return {
      authUser: JSON.parse(localStorage.getItem('userData')),
    }
  },
  methods: {
    isUnread(data) {
      if ((data.read === 'false') && (this.authUser.id !== data.originid)) {
        return true
      }
      return false
    },
  },
  setup() {
    const { resolveAvatarBadgeVariant } = useChat()
    return {
      //  formatDateToMonthShort,
      getInitials,
      getStyle,
      resolveAvatarBadgeVariant,
    }
  },
}
</script>

<style>

</style>
