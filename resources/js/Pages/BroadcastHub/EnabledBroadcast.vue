<template>
    <Recipients
        :can_add_channels="can_add_channels"
        :can_manage="can_manage"
        :broadcaster="broadcaster"
        @update:recipient="(e) => recipient = e"
    />

    <GlobalBroadcasts
        :key="recipient.recipient_id + broadcaster.id"
        :broadcaster-id="broadcaster.id"
        :recipient-id="recipient.recipient_id"
        v-if="entities.length > 0 && recipient"
    />

    <Entities
        :entities="entities"
        :activeEntity="recipient"
        v-if="entities.length > 0 && recipient"
    />

</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { chain } from 'lodash'
import Entities from "./Entities/Entities.vue";
import Recipients from "./Recipients.vue";
import GlobalBroadcasts from "./Globals/GlobalBroadcasts.vue";

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

const recipient = ref(null)
const notification_classes = ref([])
const notifications = ref([])

async function buildEndpoints() {

    let data = []

    await axios.get(route('subscriptions.index', props.broadcaster.id))
        .then(response => {
            data = response.data
        })
        .catch(error => {
            console.log(error)
        })

    notification_classes.value = data
}

async function buildEntities() {

    await Promise.all(endpoints.value.map(endpoint => axios.get(endpoint)))
        .then((responses) => {

            notifications.value = responses.map((response) => response.data).flat()

            return responses
        })
        .catch(error => {
            console.log(error)
    })
}

const endpoints = computed(() => {

    // if recipient is null, return empty array
    if (!recipient.value) {
        return []
    }

    return notification_classes.value.map((notification_class) => {
        return route('subscriptions.show', [notification_class, recipient.value.recipient_id])
    })
})

const entities = computed(() => {

    // merge owned and not owned notifications
    return chain(notifications.value)
        .groupBy('entity_id')
        .map((value, key) => {

            return {
                entity_id: key,
                entity_type: value[0].entity_type,
                owned: value[0].owned,
                notifications: value,
                num_notifications: value.length,
                num_active_subscriptions: value.filter(notification => notification.subscribed).length,
            }
        })
        .orderBy(['owned', 'num_active_subscriptions', 'num_notifications'], ['desc', 'desc', 'desc'])
        .value()

})

watch(() => recipient.value, async (newRecipient, oldRecipient) => {

    // if recipient are the same, do nothing
    if (newRecipient === oldRecipient) {
        return
    }

    notifications.value = []

    await buildEntities()
})

onMounted(async () => {

    await buildEndpoints()
})

</script>
