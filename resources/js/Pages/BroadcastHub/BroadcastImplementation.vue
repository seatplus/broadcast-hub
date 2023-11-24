<template>
    <Implementation
        :url="route('broadcasts.show', broadcaster.id)"
    >
        <template #default="{status, connector}">
            <DisabledBroadcast v-if="status === 'Disabled'" :broadcaster="broadcaster" :can_enable="connector.can_enable"/>
            <Registration :url="connector.url" v-if="status === 'Not Registered'"/>
            <EnabledBroadcast v-if="status === 'Registered'" :broadcaster="broadcaster" :can_manage="connector.can_manage" :can_add_channels="connector.can_enable"/>
        </template>

        <template #header="{status, connector}">
            <Button v-if="status === 'Registered' && connector.can_enable" method="delete" :href="route('broadcasts.destroy', broadcaster.id)">
                Disable
            </Button>
        </template>
    </Implementation>
</template>

<script setup>

import Implementation from "@/Shared/Connector/Implementation.vue"
import DisabledBroadcast from "./DisabledBroadcast.vue";
import Registration from "@/Shared/Connector/Registration.vue";
import EnabledBroadcast from "./EnabledBroadcast.vue";
import Button from '@/Shared/Layout/Button.vue'

defineProps({
    broadcaster: {
        type: Object,
        required: true
    }
})

</script>




