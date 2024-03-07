<template>
  <div class="sm:mx-auto sm:w-full sm:max-w-md px-2 sm:px-0">
    <ACard class="px-8 pb-8">
      <template #title> <span class="a-title text-center"> Login</span></template>
      <div class="sm:mx-auto sm:w-full sm:max-w-md" v-if="settings.logo_light || settings.logo_dark">
        <img :src="activeThemeName === 'dark' ? settings.logo_dark : settings.logo_light" :alt="settings.company_name"
          class="mx-auto h-12 w-auto">
      </div>
      <div class="grid gap-6">
        <AInput type="email" :disabled="loading" label="Email" placeholder="Enter your email" prepend-inner-icon="i-bx-at"
          v-model="data.email" :error="form.errors.get('email')" @focus="form.errors.clear('email')" />
        <AInput placeholder="Enter Password" :type="showPassword ? 'input' : 'password'" :disabled="loading"
          label="Password" v-model="data.password" :error="form.errors.get('password')"
          @focus="form.errors.clear('password')">
          <template #append-inner>
            <ABtn icon-only variant="text" class="rounded-full text-sm" :icon="showPassword ? 'i-bx-hide' : 'i-bx-show'"
              @click="showPassword = !showPassword"></ABtn>
          </template>
        </AInput>
        <ACheckbox v-model="data.remember" :disabled="loading" label="Remember Me" />
        <ABtn class="w-full" @click="login" :loading="loading">Login</ABtn>
      </div>

    </ACard>
    <div class="mt- text-center text-neutral-900 dark:text-white">
      Copyright © 2023
    </div>
    <!-- 
    <div class="bg-white dark:bg-neutral-800 py-8 px-6 sm:px-10 shadow rounded-lg">

    </div>
     -->
  </div>
</template>

<script setup>
import Form from '@/components/Form/Form';
import { useAnu } from 'anu-vue';

const { activeThemeName } = useAnu()

const settings = Application.config.settings

const showPassword = ref(false)

const form = new Form()

const data = ref({
  email: null,
  password: null,
  remember: false,
})

const loading = computed(() => form.busy.value)

const login = async () => {
  await Application.request().get(
    Application.config.api_url + '/sanctum/csrf-cookie'
  )

  form.set(data.value)
    .post(Application.config.url + '/login')
    .then(data => {
      window.location.href = '/'
    })
}

</script>

<route lang="json">
{
  "name": "login",
  "meta": {
    "layout": "auth",
    "requiresAuth": false
  }
}
</route>