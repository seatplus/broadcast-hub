<template>
    <Card class="max-h-screen hover:overflow-auto">
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="item in entities" :key="item.entity_id" class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                <dt>
                    <EntityByIdBlock :id="parseInt(item.entity_id)" />
                    <p class="ml-16 truncate text-sm font-medium text-gray-500">{{ item.notifications.length }} Notifications available</p>
                </dt>
                <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                    <div class="flex-1 space-x-3">
                        <span v-if="item.owned" class="inline-flex items-center rounded-full bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-700">Owned</span>
                        <span v-if="item.num_active_subscriptions > 0" class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                            <span class="text-indigo-500">
                              {{ item.num_active_subscriptions }}
                            </span>
                            active subscriptions
                          </span>
                    </div>

                    <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
                        <ManageSubscriptionModal :notifications="item.notifications" :recipient="activeEntity"/>
                    </div>
                </dd>
            </div>
        </dl>
    </Card>
</template>

<script setup>
import Card from "@/Shared/Layout/Cards/Card.vue";
import EntityByIdBlock from "@/Shared/Layout/Eve/EntityByIdBlock.vue"
import ManageSubscriptionModal from "./ManageSubscriptionModal.vue";

defineProps({
    entities: {
        type: Array,
        required: true
    },
    activeEntity: {
        type: Object,
        required: true
    },
})
</script>

<style scoped>

</style>
