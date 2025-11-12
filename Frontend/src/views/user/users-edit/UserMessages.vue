<template>
  <div>
    <br>
    <div class="d-flex">
      <feather-icon
        icon="MessageSquareIcon"
        size="19"
      />
      <h4 class="mb-0 ml-50">
        User Messages
      </h4>
    </div>
    <br>
    <b-row
      style="background: #f6f6f6;"
    >
      <b-col
        cols="12"
        md="12"
      >
        <br>
        <b-button
          v-b-modal.modal-startConversation
          variant="primary"
          class="mb-1 mb-sm-0 mr-0 mr-sm-1"
          style="float:right;"
        >
          <feather-icon
            icon="PlusCircleIcon"
            size="15"
          />
          Start Conversation
        </b-button>
        <br><br><br>
        <profile-post :posts="posts" />
      </b-col>
    </b-row>
    <!-- select 2 demo -->
    <b-modal
      id="modal-startConversation"
      title="Start Conversation"
      ok-title="Post"
      cancel-variant="outline-secondary"
      size="lg"
    >
      <b-form>
        <b-form-group
          label="Content Title"
          label-for="name"
        >
          <b-form-input
            id="name"
            placeholder="Enter Content Title"
          />
        </b-form-group>
        <b-form-group
          label="Content Text"
          label-for="zip-code"
        >
          <b-form-textarea
            id="textarea"
            v-model="text"
            placeholder="Enter something..."
            rows="8"
            max-rows="12"
          />
        </b-form-group>
      </b-form>
    </b-modal>
  </div>
</template>

<script>
import {
  BRow, BCol, BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormTextarea,
} from 'bootstrap-vue'

import Ripple from 'vue-ripple-directive'

import ProfilePost from './ProfilePost.vue'

export default {
  components: {
    BRow, BCol, BButton, BModal, BForm, BFormInput, BFormGroup, ProfilePost, BFormTextarea,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  data() {
    return {
      posts: [
        {
          avatar: '',
          username: 'Borut Grauf',
          postTime: '12 Dec 2020 at 1:16 AM',
          postTitle: 'Content Title',
          postText:
            'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
          postImg: '',
          comments: 2,
          detailedComments: [
            {
              avatar: '',
              username: 'John Doe',
              comment: 'Easy & smart fuzzy search functionality which enables users to search quickly.',
            },
            {
              avatar: '',
              username: 'Borut Grauf',
              comment: 'Unlimited color options allows you to set your application color as per your branding.',
            },
          ],
        },
        {
          avatar: '',
          username: 'Borut Grauf',
          postTime: '12 Dec 2020 at 1:16 AM',
          postTitle: 'Content Title',
          postText:
            'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
          postImg: '',
          comments: 0,
        },
      ],
    }
  },
}
</script>

<style>

</style>
