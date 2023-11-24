<template>
    <SwitchGroup as="div" class="flex items-center justify-between">
                <span class="flex flex-grow flex-col">
                    <SwitchLabel as="span" class="text-sm font-medium leading-6 text-gray-900" passive>{{ notification.title }}</SwitchLabel>
                    <SwitchDescription as="span" class="text-sm text-gray-500">{{ notification.description }}.</SwitchDescription>
                </span>
        <Switch v-model="show" :class="[show ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2']">
            <span aria-hidden="true" :class="[show ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out']" />
        </Switch>
    </SwitchGroup>
</template>

<script setup>
import { Switch, SwitchDescription, SwitchGroup, SwitchLabel } from '@headlessui/vue'
import axios from 'axios'
import { ref, watch } from "vue";

const props = defineProps({
    notification: {
        type: Object,
        required: true
    },
    recipient: {
        type: Object,
        required: true
    }
})

const show = ref(props.notification.subscribed)

watch(() => show.value, async (newShow) => {
    if(newShow) {
        await subscribe(props.notification)
    } else {
        await unsubscribe(props.notification)
    }
})

const subscribe = async (notification) => {

    await axios.post(route('subscriptions.store'), {
        recipient_id: props.recipient.recipient_id,
        ...notification
    }).catch(error => {
        console.log(error)
        show.value = false
    }).then((response) => {
        props.notification.subscription_id = response.data.subscription_id
        props.notification.subscribed = true
    })
}

const unsubscribe = async (notification) => {
    await axios.delete(route('subscriptions.destroy', notification.subscription_id)).then(() => {
        props.notification.subscribed = false
        props.notification.subscription_id = null
    }).catch(error => {
        console.log(error)
        show.value = true
    })
}

</script>

<style scoped>

</style>
