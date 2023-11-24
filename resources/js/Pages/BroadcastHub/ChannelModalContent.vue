<template>
    <form @submit.prevent="store">
        <fieldset>
            <legend class="text-base font-semibold leading-6 text-gray-900">Channels</legend>
            <div class="mt-4 divide-y divide-gray-200 border-b border-t border-gray-200">
                <div v-for="channel in channels" :key="channel.id" class="relative flex items-start py-4">
                    <div class="min-w-0 flex-1 text-sm leading-6">
                        <label :for="`channel-${channel.id}`" class="select-none font-medium text-gray-900">{{ channel.name }}</label>
                    </div>
                    <div class="ml-3 flex h-6 items-center">
                        <input
                            :id="`channel-${channel.id}`"
                            :name="`channel-${channel.id}`"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                            :value="channel"
                            v-model="form.checkedChannels"
                        />
                    </div>
                </div>
            </div>
        </fieldset>
        <div class="mt-6 flex items-center justify-end gap-x-6">
            <button type="submit" @click="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</template>

<script setup>
import {defineProps, ref, onMounted} from 'vue'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'


const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
        required: true
    },
    broadcaster: {
        type: Object,
        required: true
    },
})

const emit = defineEmits(['update:modelValue', 'close'])

const channels = ref([])

const form = useForm({
    checkedChannels: props.modelValue.map(channel => {
        return {
            id: channel.connector_id,
            name: channel.name
        }
    }),
})

const store = () => {

    let checkedChannels = form.checkedChannels

    form.put(route('channels.store', props.broadcaster.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:modelValue', checkedChannels)
            emit('close')
        }
    })
}

onMounted(() => {
    axios.get(route('channels.index', props.broadcaster.id))
        .then(response => {
            channels.value = response.data
        })
        .catch(error => {
            console.log(error)
        })

})

</script>

<style scoped>

</style>
