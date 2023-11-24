<template>
    <div class="space-y-3">
        <PageHeader :page-title="pageTitle" />

        <BroadcastImplementation
            v-for="broadcaster in broadcasters"
            :broadcaster="broadcaster"
        />

    </div>
</template>

<script setup>
import PageHeader from "@/Shared/Layout/PageHeader.vue"
import BroadcastImplementation from "./BroadcastImplementation.vue"
const pageTitle = 'Broadcast Hub';

defineProps({
    broadcasters: {
        type: Object,
        required: true
    }
})

</script>

