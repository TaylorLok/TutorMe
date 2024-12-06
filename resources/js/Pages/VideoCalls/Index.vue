<template>
    <AuthenticatedLayout>
      <template #header>
        <div class="flex justify-between items-center">
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">Video Calls</h2>
          <Link
            :href="route('video-calls.create')"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          >
            Schedule Call
          </Link>
        </div>
      </template>
  
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <div v-if="videoCalls.length" class="space-y-4">
                <div v-for="call in videoCalls" :key="call.id" 
                     class="border p-4 rounded-lg flex justify-between items-center">
                  <div>
                    <p class="font-medium">
                      {{ isUserCaller(call) ? 
                        `Call with: ${call.receiver.name}` : 
                        `Call from: ${call.caller.name}` }}
                    </p>
                    <p class="text-gray-600">
                      Scheduled: {{ formatDate(call.scheduled_at) }}
                    </p>
                    <span 
                      :class="{
                        'px-2 py-1 rounded text-sm': true,
                        'bg-yellow-100 text-yellow-800': call.call_status === 'scheduled',
                        'bg-green-100 text-green-800': call.call_status === 'in-progress',
                        'bg-blue-100 text-blue-800': call.call_status === 'completed',
                        'bg-red-100 text-red-800': call.call_status === 'cancelled'
                      }"
                    >
                      {{ call.call_status }}
                    </span>
                  </div>
                  <Link
                    :href="route('video-calls.show', call.id)"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                  >
                    Join Call
                  </Link>
                </div>
              </div>
              <div v-else class="text-center text-gray-500">
                No video calls scheduled.
              </div>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
</template>
  
<script setup>
  import { Link, usePage } from '@inertiajs/vue3';
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
  
  const props = defineProps({
    videoCalls: Array
  });
  
  const isUserCaller = (call) => {
    return call.caller_id === usePage().props.auth.user.id;
  };
  
  const formatDate = (date) => {
    return new Date(date).toLocaleString();
  };
</script>