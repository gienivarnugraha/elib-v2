<template>
  <Notify />
  <router-view></router-view>
</template>

<script setup>

onMounted(() => {
  Application.$on('error', message => {
    if (message) {
      Application.error(message)
    }
  })

  Application.$on('conflict', message => {
    if (message) {
      Application.error(message)
    }
  })

  Application.$on('form-validation-errors', message => {
    if (message) {
      Application.error(message)
    }
  })

  Application.$on('token-expired', message => {
    if (message) {
      Application.error(message)
    }
  })

})

</script>
