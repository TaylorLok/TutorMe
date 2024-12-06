<template>
    <AuthenticatedLayout>
      <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Schedule Video Call</h2>
      </template>
  
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <form @submit.prevent="submit" class="space-y-6">
                <div>
                  <InputLabel for="receiver_id" value="Select User to Call" />
                  <select
                    id="receiver_id"
                    v-model="form.receiver_id"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                  >
                    <option value="">Select a user</option>
                    <option 
                      v-for="user in users" 
                      :key="user.id" 
                      :value="user.id"
                    >
                      {{ user.label }}
                    </option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.receiver_id" />
                </div>
  
                <div>
                  <InputLabel for="scheduled_at" value="Schedule Date & Time" />
                  <input
                    id="scheduled_at"
                    type="datetime-local"
                    v-model="form.scheduled_at"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                  />
                  <InputError class="mt-2" :message="form.errors.scheduled_at" />
                </div>
  
                <div class="flex items-center justify-end mt-4">
                  <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Schedule Call
                  </PrimaryButton>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
</template>
  
<script setup>
  import { useForm } from '@inertiajs/vue3';
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import InputError from '@/Components/InputError.vue';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  
  defineProps({
    users: Array
  });
  
  const form = useForm({
    receiver_id: '',
    scheduled_at: ''
  });
  
  const submit = () => {
    form.post(route('video-calls.store'));
  };
</script>