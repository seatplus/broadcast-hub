<template>
    <div class="text-sm">
        <span @click="show = !show" class="font-medium text-indigo-600 hover:text-indigo-500 hover:cursor-pointer">Manage Subscriptions</span>
    </div>

    <WithDismissButtonModal v-model="show">
        <div class="mt-3 sm:mt-0">
            <DialogTitle
                as="h3"
                class="text-lg leading-6 font-medium text-gray-900"
            >
                Subscribe to Notifications
            </DialogTitle>
        </div>
        <div class="mt-2 grid grid-cols-1 gap-2">
            <NotificationSwitch v-for="notification in orderedNotifications" :recipient="recipient" :notification="notification" />
        </div>
    </WithDismissButtonModal>
</template>

<script setup>
import WithDismissButtonModal from "@/Shared/Modals/WithDismissButtonModal.vue";
import { ref, computed } from 'vue'
import { orderBy } from 'lodash'
import NotificationSwitch from "./NotificationSwitch.vue";
import { DialogTitle } from "@headlessui/vue";

const show = ref(false)

const props = defineProps({
    notifications: {
        type: Array,
        required: true,
        default: () => []
    },
    recipient: {
        type: Object,
        required: true
    }
})

const orderedNotifications = computed(() => {

    // order it by subscription
    return orderBy(props.notifications, 'subscribed', 'desc')
})

</script>

