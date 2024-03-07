import { defineStore } from 'pinia'

export const useConfigStore = defineStore('config', {
    state: () => ({
        sideBar: true,
        notifications: [],
        menus: [],
        user: {},
        settings: {}
    }),
    getters: {
        latestNotifications: (state) => state.notifications.latest,
        unreadNotifications: (state) => state.notifications.unread_count,
        quickCreateMenus: (state) => state.menus.filter(menu => menu.inQuickCreate),
    },
    actions: {
        setSideBar(value) {
            if (value) this.sideBar = value
            this.sideBar = !this.sideBar
        },
    }


})