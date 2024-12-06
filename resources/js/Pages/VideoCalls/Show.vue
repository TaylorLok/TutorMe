<template>
    <AuthenticatedLayout>
      <template #header>
        <div class="flex justify-between items-center">
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Video Call with {{ participantName }}
          </h2>
          <div class="flex items-center gap-4">
            <span 
              :class="{
                'px-3 py-1 rounded-full text-sm': true,
                'bg-yellow-100 text-yellow-800': videoCall.call_status === 'scheduled',
                'bg-green-100 text-green-800': videoCall.call_status === 'in-progress',
                'bg-blue-100 text-blue-800': videoCall.call_status === 'completed',
                'bg-red-100 text-red-800': videoCall.call_status === 'cancelled'
              }"
            >
              {{ videoCall.call_status }}
            </span>
          </div>
        </div>
      </template>
  
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <!-- Debug info -->
              <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                {{ errorMessage }}
              </div>
  
              <!-- Video Grid -->
              <div class="grid grid-cols-2 gap-4">
                <!-- Local Stream (Your Video) -->
                <div class="relative">
                  <div class="bg-black rounded-lg aspect-video" id="local-stream"></div>
                  <span class="absolute bottom-2 left-2 bg-black/50 text-white px-2 py-1 rounded text-sm">
                    You
                  </span>
                </div>
  
                <!-- Remote Stream (Other Participant) -->
                <div class="relative">
                  <div class="bg-black rounded-lg aspect-video" id="remote-stream"></div>
                  <span class="absolute bottom-2 left-2 bg-black/50 text-white px-2 py-1 rounded text-sm">
                    {{ participantName }}
                  </span>
                  <div v-if="!isRemoteStreamActive" 
                       class="absolute inset-0 flex items-center justify-center text-white bg-black/50">
                    Waiting for {{ participantName }} to join...
                  </div>
                </div>
              </div>
  
              <!-- Controls -->
              <div class="mt-6 flex justify-center gap-4">
                <button 
                  @click="requestPermissions"
                  v-if="!hasInitialized"
                  class="bg-blue-500 text-white p-3 rounded-full hover:bg-blue-600"
                >
                  Start Video Call
                </button>
                
                <template v-else>
                  <button 
                    @click="toggleAudio"
                    class="p-3 rounded-full transition"
                    :class="isAudioEnabled ? 'bg-gray-200 hover:bg-gray-300' : 'bg-red-500 text-white hover:bg-red-600'"
                  >
                    <span v-if="isAudioEnabled">Mute</span>
                    <span v-else>Unmute</span>
                  </button>
  
                  <button 
                    @click="toggleVideo"
                    class="p-3 rounded-full transition"
                    :class="isVideoEnabled ? 'bg-gray-200 hover:bg-gray-300' : 'bg-red-500 text-white hover:bg-red-600'"
                  >
                    <span v-if="isVideoEnabled">Stop Video</span>
                    <span v-else>Start Video</span>
                  </button>
  
                  <button 
                    @click="endCall"
                    class="bg-red-500 text-white p-3 rounded-full hover:bg-red-600"
                  >
                    End Call
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  </template>
  
  <script setup>
  import { ref, onMounted, onUnmounted, computed } from 'vue'
  import { useForm, usePage } from '@inertiajs/vue3'
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
  import AgoraRTC from 'agora-rtc-sdk-ng'
  
  const props = defineProps({
    videoCall: Object,
    agoraToken: String,
    agoraAppId: String,
    channel: String,
    uid: Number
  })
  
  // State
  const errorMessage = ref('')
  const isAudioEnabled = ref(true)
  const isVideoEnabled = ref(true)
  const hasInitialized = ref(false)
  const isLocalStreamActive = ref(false)
  const isRemoteStreamActive = ref(false)
  
  // Initialize Agora client
  const client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' })
  let localAudioTrack
  let localVideoTrack
  
  // Computed
  const currentUser = computed(() => usePage().props.auth.user)
  const participantName = computed(() => {
    const currentUserId = currentUser.value.id
    return props.videoCall.caller_id === currentUserId
      ? props.videoCall.receiver.name
      : props.videoCall.caller.name
  })
  
  // Methods
  const requestPermissions = async () => {
    try {
      console.log('Requesting media permissions...')
      await navigator.mediaDevices.getUserMedia({ video: true, audio: true })
      await initializeAgora()
      hasInitialized.value = true
    } catch (error) {
      errorMessage.value = `Failed to access camera/microphone: ${error.message}`
      console.error('Permission error:', error)
    }
  }
  
  const initializeAgora = async () => {
    try {
      console.log('Initializing Agora with:', {
        appId: props.agoraAppId?.substring(0, 4) + '...',
        channel: props.channel,
        uid: props.uid,
        hasToken: !!props.agoraToken
      })
  
      await client.join(
        props.agoraAppId,
        props.channel,
        props.agoraToken,
        props.uid
      )
      console.log('Successfully joined Agora channel')
  
      localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack()
      console.log('Audio track created')
      
      localVideoTrack = await AgoraRTC.createCameraVideoTrack()
      console.log('Video track created')
  
      await client.publish([localAudioTrack, localVideoTrack])
      console.log('Published local tracks')
  
      localVideoTrack.play('local-stream')
      isLocalStreamActive.value = true
      console.log('Playing local video')
  
      if (props.videoCall.call_status === 'scheduled') {
        useForm({}).patch(route('video-calls.update-status', props.videoCall.id), {
          preserveScroll: true,
          data: { status: 'in-progress' }
        })
      }
    } catch (error) {
      console.error('Agora initialization failed:', error)
      errorMessage.value = `Failed to initialize: ${error.message}`
    }
  }
  
  const toggleAudio = () => {
    if (localAudioTrack) {
      isAudioEnabled.value = !isAudioEnabled.value
      localAudioTrack.setEnabled(isAudioEnabled.value)
    }
  }
  
  const toggleVideo = () => {
    if (localVideoTrack) {
      isVideoEnabled.value = !isVideoEnabled.value
      localVideoTrack.setEnabled(isVideoEnabled.value)
    }
  }
  
  const endCall = async () => {
    try {
      localAudioTrack?.close()
      localVideoTrack?.close()
      await client.leave()
  
      useForm({}).patch(route('video-calls.update-status', props.videoCall.id), {
        preserveScroll: true,
        data: { status: 'completed' }
      })
  
      window.location = route('video-calls.index')
    } catch (error) {
      console.error('Failed to end call:', error)
      errorMessage.value = `Failed to end call: ${error.message}`
    }
  }
  
  // Event Listeners
  client.on('user-published', async (user, mediaType) => {
    try {
      await client.subscribe(user, mediaType)
      if (mediaType === 'video') {
        user.videoTrack.play('remote-stream')
        isRemoteStreamActive.value = true
      }
      if (mediaType === 'audio') {
        user.audioTrack.play()
      }
    } catch (error) {
      console.error('Remote user connection error:', error)
      errorMessage.value = `Failed to connect with remote user: ${error.message}`
    }
  })
  
  client.on('user-unpublished', (user, mediaType) => {
    if (mediaType === 'video') {
      isRemoteStreamActive.value = false
    }
  })
  
  // Lifecycle
  onUnmounted(async () => {
    localAudioTrack?.close()
    localVideoTrack?.close()
    await client.leave()
  })
  </script>