<template>
    <BarWithUnderline :key="date" :tabs="recipient_tabs" @select="select" v-if="recipient_tabs.length > 1"/>

    <WithDismissButtonModal v-model="show_modal">
        <ChannelModalContent v-model="recipients" :broadcaster="broadcaster" @close="() => show_modal = false"/>
    </WithDismissButtonModal>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'
import BarWithUnderline from "@/Shared/Layout/Tabs/BarWithUnderline.vue";
import WithDismissButtonModal from "@/Shared/Modals/WithDismissButtonModal.vue";
import ChannelModalContent from "./ChannelModalContent.vue";

const props = defineProps({
    broadcaster: {
        type: Object,
        required: true
    },
    can_manage: {
        type: Boolean,
        required: true
    },
    can_add_channels: {
        type: Boolean,
        required: true
    }
})

// define emitters
const emit = defineEmits(['update:recipient'])
const date = ref(+new Date())

const active_recipient = ref(null)
const show_modal = ref(false)

const user_recipient = ref(null)
const recipients = ref([])

function select(tab) {
    active_recipient.value = tab

    if(tab.id === -1) {
        show_modal.value = true
        return
    }

    emit('update:recipient', tab)
}

async function getRecipients() {
    await axios.get(route('recipients.show', props.broadcaster.id))
        .then(response => {

            // set user recipients
            user_recipient.value = {
                recipient_id: response.data.user_recipient.id,
                connector_id: response.data.user_recipient.connector_id,
                name: response.data.user_recipient.name + ' (You)'
            }

            // set active recipient
            active_recipient.value = user_recipient.value
            emit('update:recipient', user_recipient.value)

            //set recipients
            recipients.value = response.data.recipients.map((recipient) => {
                return {
                    recipient_id: recipient.id,
                    connector_id: recipient.connector_id,
                    name: recipient.name
                }
            })
        })
        .catch(error => {
            console.log(error)
        })
}

const recipient_tabs = computed(() => {
    // combine user recipients and recipients
    let tabs = [user_recipient.value, ...recipients.value]

    // if tabs is an array of length 1 and the first element is null, set tabs to an empty array
    if(tabs.length === 1 && tabs[0] === null) {
        tabs = []
    }

    // add index to tabs
    tabs = tabs.map((tab, index) => {
        tab.id = index
        return tab
    })

    // if can add channels, append '+' to tabs
    if(props.can_add_channels) {
        tabs.push({ id: -1, name: '...' })
    }

    return tabs
})

watch(() => show_modal.value, (newShowModal) => {
    if(!newShowModal) {
        // reset active recipient
        active_recipient.value = user_recipient.value

        // increment date
        date.value++
    }
})

watch(() => active_recipient.value, (newActiveRecipient) => {
    if(!newActiveRecipient.id === -1) {

        emit('update:recipient', newActiveRecipient)
    }
})

onMounted(async () => {
    await getRecipients()
})
</script>

<style scoped>

</style>
