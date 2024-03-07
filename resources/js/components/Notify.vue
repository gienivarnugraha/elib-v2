<template>
    <notification-group group="app">
        <div class="notifications fixed inset-0 flex items-start justify-end p-6 px-4 py-6 pointer-events-none">
            <div class="w-full max-w-sm">
                <notification v-slot="{ notifications, close }" enter="ease-out duration-300 transition"
                    enter-from="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                    enter-to="translate-y-0 opacity-100 sm:translate-x-0" leave="transition ease-in duration-100"
                    leave-from="opacity-100" leave-to="opacity-0" move="transition duration-500" move-delay="delay-300">
                    <div v-for="(notification, index) in notifications" :key="index" class="notification relative max-w-sm w-full bg-white dark:bg-neutral-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden mb-2">


                        <AAlert dismissible v-if="notification.type === 'success' || notification.type === 'info'"
                            variant="light"
                            :icon="notification.type === 'success' ? 'i-bx-check-circle' : 'i-bx-info-circle'"
                            :color="notification.type === 'success' ? 'success' : 'info'"
                            @click:append-icon="close(notification.id)">
                            <span>{{ notification.text }}</span>
                        </AAlert>

                        <AAlert v-if="notification.type === 'error'" dismissible variant="fill" color="danger"
                            icon="i-bx-error h-5" append-icon="i-bx-x" @click:append-icon="close(notification.id)">
                            <span>{{ notification.text }}</span>
                        </AAlert>

                    </div>
                </notification>
            </div>
        </div>
    </notification-group>
</template>

<style lang="css">

.notification,
.notifications {
  z-index: 1150;
}
</style>