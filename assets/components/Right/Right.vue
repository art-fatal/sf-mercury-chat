<template>
  <div class="col-7 px-0">
    <div class="px-4 py-5 chat-box bg-white">
      <!-- Sender Message-->
      <SenderMessage/>

      <!-- Reciever Message-->
      <ReceiverMessage/>
    </div>

    <!-- Typing area -->
    <InputMessage/>

  </div>
</template>

<script>
import SenderMessage from "./SenderMessage";
import ReceiverMessage from "./ReceiverMessage";
import InputMessage from "./InputMessage";

export default {
  components: {InputMessage, ReceiverMessage, SenderMessage}
}
</script>