<script setup>
import {ref, watch} from 'vue'
import { Switch, SwitchDescription, SwitchGroup, SwitchLabel } from '@headlessui/vue'
import {useToasts} from "@/Functions/useToasts.js";

const props = defineProps({
    notification: {
        type: Object,
        required: true
    }
})

const enabled = ref(props.notification.is_subscribed)
const processing = ref(false)

const toasts = useToasts()

const subscribe = async (notification) => {

    await axios.post(route('subscriptions.store'), notification)
        .catch(error => {
            console.log(error)
        }).then((response) => {
            props.notification.subscription_id = response.data.subscription_id
            props.notification.is_subscribed = true
            toasts.addToast('Subscribed to ' + props.notification.title, {appearance: 'success'})
    })
}

const unsubscribe = async (notification) => {
     await axios.delete(route('subscriptions.destroy', notification.subscription_id)).then(() => {
        props.notification.is_subscribed = false
        props.notification.subscription_id = null
        toasts.addToast('Unsubscribed from ' + props.notification.title, {appearance: 'success'})
    }).catch(error => {
        console.log(error)
    })
}

watch(() => enabled.value, async (newShow) => {

    flipProcessing()
    
    if(newShow) {
        await subscribe(props.notification)
    } else {
        await unsubscribe(props.notification)
    }

    flipProcessing()
})

const flipProcessing = () => {
    processing.value = !processing.value
}

</script>

<template>
    <div class="bg-white shadow sm:rounded-lg">
        <SwitchGroup as="div" class="px-4 py-5 sm:p-6">
            <SwitchLabel as="h2" class="text-lg font-medium leading-6 text-gray-900 sm:truncate sm:tracking-tight" passive>{{ notification.title }}</SwitchLabel>
            <div class="mt-2 sm:flex sm:items-start sm:justify-between">
                <div class="max-w-xl text-sm font-medium text-gray-500">
                    <SwitchDescription>{{ notification.description }}</SwitchDescription>
                </div>
                <div class="mt-5 sm:ml-6 sm:mt-0 sm:flex sm:flex-shrink-0 sm:items-center">
                    <Switch v-if="!processing" v-model="enabled" :class="[enabled ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2']">
                        <span aria-hidden="true" :class="[enabled ? 'translate-x-5' : 'translate-x-0', 'inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out']" />
                    </Switch>
                </div>
            </div>
        </SwitchGroup>
    </div>
</template>
