<script setup>

import Card from "@/Shared/Layout/Cards/Card.vue";
import { ref, onMounted } from "vue";
import axios from 'axios'
import GlobalNotification from "./GlobalNotification.vue";

const props = defineProps({
    broadcasterId: {
        type: String,
        required: true
    },
    recipientId: {
        type: String,
        required: true
    }
})

const hasGlobals = ref(false)
const isLoaded = ref(false)
const notifications = ref([])

onMounted(() => {

    axios.get(route('global-subscriptions.index', [props.broadcasterId, props.recipientId])).then(response => {
        notifications.value = response.data
        hasGlobals.value = response.data.length > 0
    }).finally(() => {
        isLoaded.value = true
    })

})

</script>

<template>
    <Card>
        <div v-if="!isLoaded"
            class="relative block w-full border-2 border-gray-300 border-dashed rounded-lg p-12 text-center focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <svg
                class="animate-spin mx-auto h-12 w-12 text-gray-400"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
            </svg>
            <span class="mt-2 block text-sm font-medium text-gray-900">
          loading global subscriptions...
        </span>
        </div>
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <GlobalNotification v-for="notification in notifications" :key="notification.notification" :notification="notification" />
        </dl>

    </Card>

</template>
