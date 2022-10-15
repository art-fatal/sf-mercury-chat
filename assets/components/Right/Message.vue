<template>
  <div class="media w-50 mb-3" :class="{'ml-auto' : message.mine}">
    <img v-if="!message.mine" src="https://bootstrapious.com/i/snippets/sn-chat/avatar.svg" alt="user" width="50" class="rounded-circle">
    <div class="media-body ml-3">
      <div :class="[message.mine ? 'bg-primary' : 'bg-light']" class=" rounded py-2 px-3 mb-2">
        <p :class="[message.mine ? 'text-white' : 'text-muted']" class="text-small mb-0">{{ message.content }}</p>
      </div>
      <p class="small">{{ date }}</p>
    </div>
  </div>
</template>
<script>
export default {
  name: 'Message',
  props:{
    message: Object
  },
  computed: {
    date() {
      return new Date(this.message.createdAt).toLocaleString();
    }
  }
}
</script>