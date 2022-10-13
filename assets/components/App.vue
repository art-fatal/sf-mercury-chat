<template>
  <div class="container py-5 px-4">
    <div class="row rounded-lg overflow-hidden shadow">
      <!-- Users box-->
      <Left />
      <router-view :key="$route.fullPath"></router-view>
      <!-- Chat Box-->
    </div>
  </div>

</template>

<script>
import Left from "./Left/Left";
import Right from "./Right/Right";

export default {
  components: {Right, Left},
}
</script>