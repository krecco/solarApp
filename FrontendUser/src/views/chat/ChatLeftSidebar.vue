<template>
  <div class="sidebar-left">
    <div class="sidebar">

      <!-- Logged In User Profile Sidebar -->
      <user-profile-sidebar
        :shall-show-user-profile-sidebar="shallShowUserProfileSidebar"
        :profile-user-data="profileUserData"
        @close-sidebar="$emit('update:shall-show-user-profile-sidebar', false)"
      />

      <!-- Sidebar Content -->
      <div
        class="sidebar-content"
        :class="{'show': mqShallShowLeftSidebar}"
      >

        <!-- Sidebar close icon -->
        <span class="sidebar-close-icon">
          <feather-icon
            icon="XIcon"
            size="16"
            @click="$emit('update:mq-shall-show-left-sidebar', false)"
          />
        </span>

        <!-- Header -->
        <div class="chat-fixed-search">
          <div class="d-flex align-items-center justify-content-between w-100">
            <div class="sidebar-profile-toggle">
              <h6 class="mb-0">
                {{ $t('Messaging') }}
              </h6>
            </div>
            <feather-icon
              icon="EditIcon"
              size="24"
              class="text-muted cursor-pointer"
              @click="createNewConv()"
            />
          </div>
        </div>
        <div class="chat-fixed-search">
          <div class="d-flex align-items-center w-100">
            <!-- Search -->
            <b-input-group class="input-group-merge w-100 rouded">
              <b-input-group-prepend is-text>
                <feather-icon
                  icon="SearchIcon"
                  class="text-muted"
                />
              </b-input-group-prepend>
              <b-form-input
                v-model="searchQuery"
                :placeholder="$t('Search...')"
              />
            </b-input-group>
          </div>
        </div>
        <!-- ScrollArea: Chat & Contacts -->

          <!-- Chats -->
          <!--
          <ul
            v-if="filteredChatsContacts"
            class="chat-users-list chat-list media-list"
          >
            <chat-contact
              v-for="contact in filteredChatsContacts.value"
              :key="contact.contact.id"
              :user="contact"
              tag="li"
              :class="{'active-left-border': activeChatContactId === contact.contact.id}"
              is-chat-contact
              @click="$emit('open-chat', contact.contact.id), closeNewConv()"
            />
          </ul>
          -->
        <vue-perfect-scrollbar
          :settings="perfectScrollbarSettings"
          class="chat-user-list-wrapper list-group scroll-area"
        >
          <ul
            class="chat-users-list chat-list media-list"
          >
            <chat-contact
              v-for="contact in chatsContacts"
              :key="contact.contactid"
              tag="li"
              :user="contact"
              :class="{'active-left-border': activeChatContactId === contact.contactid}"
              is-chat-contact
              @click="$emit('open-chat', contact.contactid), closeNewConv()"
            />
          </ul>
          <!--
          <div
            v-for="contact in chatsContacts"
            :key="contact.contactid"
            tag="li"
            :user="contact"
            :class="{'active-left-border': activeChatContactId === contact.contactid}"
            is-chat-contact
            @click="$emit('open-chat', contact.contactid), closeNewConv()"
          >
            ena
            {{contact.contactid}}
            {{contact.message}}
            {{contact.t0}}
            <br/><br/>
          </div>
          -->
        </vue-perfect-scrollbar>
      </div>

    </div>
  </div>
</template>

<script>
import {
  BInputGroup, BInputGroupPrepend, BFormInput,
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import { ref, computed } from '@vue/composition-api'
import ChatContact from './ChatContact.vue'
import UserProfileSidebar from './UserProfileSidebar.vue'

export default {
  components: {

    // BSV
    BInputGroup,
    BInputGroupPrepend,
    BFormInput,

    // 3rd partychatsContacts
    // SFC
    ChatContact,
    UserProfileSidebar,
  },
  props: {
    chatsContacts: {
      type: Array,
      required: true,
    },
    contacts: {
      type: Array,
      required: true,
    },
    shallShowUserProfileSidebar: {
      type: Boolean,
      required: true,
    },
    profileUserData: {
      type: Object,
      required: true,
    },
    profileUserMinimalData: {
      type: Object,
      required: true,
    },
    activeChatContactId: {
      type: String,
      default: null,
    },
    mqShallShowLeftSidebar: {
      type: Boolean,
      required: true,
    },
  },
  watch: {
    chatsContacts() {
      if (this.$props.chatsContacts.length > 0) {
        this.filteredChatsContacts = computed(() => this.$props.chatsContacts.filter(this.searchFilterFunction))
      }
      if (this.$props.contacts.length > 0) {
        this.filteredContacts = computed(() => this.$props.contacts.filter(this.searchFilterFunction))
      }
    },
  },
  methods: {
    createNewConv() {
      this.$emit('open-new-conversation')
    },
    closeNewConv() {
      this.$emit('close-new-conversation')
    },
  },
  setup(props) {
    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    let filteredChatsContacts = null
    let filteredContacts = null
    const resolveChatContact = userId => props.contacts.find(contact => contact.id === userId)

    // Search Query
    const searchQuery = ref('')
    const searchFilterFunction = contact => {
      if (typeof contact.fullName !== 'undefined') {
        contact.fullName.toLowerCase().includes(searchQuery.value.toLowerCase())
      }
    }
    if (props.chatsContacts.length > 0) {
      filteredChatsContacts = computed(() => props.chatsContacts.filter(searchFilterFunction))
    }
    if (props.contacts.length > 0) {
      filteredContacts = computed(() => props.contacts.filter(searchFilterFunction))
    }

    console.log('filteredChatsContacts')
    console.log(filteredChatsContacts)
    console.log(props.chatsContacts)

    return {
      // Search Query
      searchQuery,
      searchFilterFunction,
      filteredChatsContacts,
      filteredContacts,

      // UI
      resolveChatContact,
      perfectScrollbarSettings,
      VuePerfectScrollbar,
    }
  },
}
</script>

<style lang="scss" scoped>
  .active-left-border{
    box-shadow: 4px 0 0 #7367f0 inset;
  }
</style>
