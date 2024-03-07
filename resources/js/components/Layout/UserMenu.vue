<template>
  <div class="flex">
    <ABtn :variant="sideBar ? 'light' : 'text'" class="w-full h-16 " :append-icon="sideBar ? 'i-bx-down-arrow' : null"
      @click="show = !show">
      <span class="flex min-w-0 flex-1 justify-center">
        <span class="flex-shrink-0 h-10 w-10 ">
          <AAvatar :src="user.avatar" class="h-10 w-10" />
        </span>
        <span class="flex flex-col mx-1 justify-center items-start" v-show="sideBar">
          <span class="truncate text-sm font-medium text-neutral-800 dark:text-white">{{ user.name }}</span>
          <span class="truncate text-sm text-neutral-600 dark:text-neutral-300">{{ user.email }}</span>
        </span>
      </span>
    </ABtn>
    <AMenu class="w-48" v-model="show">
      <AList>
        <AListItem><span class="text-primary"> Notifications </span></AListItem>

        <div class="h-separator"> </div>

        <AListItem>Notifications </AListItem>
        <AListItem>Notifications </AListItem>
        <AListItem>Notifications </AListItem>
        <AListItem>Notifications </AListItem>

        <span class="h-separator"></span>
        <AListItem @click="logout"> <i class="i-bx-log-out"></i> Logout </AListItem>
      </AList>
    </AMenu>
  </div>
</template>

<script setup>
import { useConfigStore } from '@/store/config'

const { user, sideBar } = storeToRefs(useConfigStore())

const show = ref(false)


const router = useRouter()

const logout = async () => {
  await Application.request().post(Application.config.url + '/logout')
  router.push('/login')
}
</script>