<template>
    <div>
        <h1>Video Call</h1>
        <div id="video"></div>
        <button @click="startCall">Start Call</button>
    </div>
</template>

<script>
import { createClient, createMicrophoneAndCameraTracks } from 'agora-rtc-sdk-ng';

export default {
    data() {
        return {
            client: null,
            channelName: 'test',
            appId: import.meta.env.VITE_AGORA_APP_ID,
        };
    },
    methods: {
        async startCall() {
            this.client = createClient({ mode: 'rtc', codec: 'vp8' });
            await this.client.join(this.appId, this.channelName, null, null);
            const tracks = await createMicrophoneAndCameraTracks();
            tracks[1].play('video');
            await this.client.publish(tracks);
        },
    },
};
</script>
