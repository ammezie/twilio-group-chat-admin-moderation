<template>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">Members</div>

                    <div class="card-body">
                        <!-- Members list goes here -->
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ channel.friendlyName }}</div>

                    <div class="card-body">
                        <div v-for="message in messages" :key="message.id">
                            <div :class="{ 'text-right': message.author === user.username }">
                                {{ message.body }}
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <input
                            type="text"
                            v-model="newMessage"
                            class="form-control"
                            placeholder="Type your message..."
                            @keyup.enter="sendMessage"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "ChatRoom",
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            messages: [],
            newMessage: '',
            channel: ''
        }
    },
    async created() {
        const token = await this.fetchToken()

        await this.initializeClient(token)
        await this.fetchMessages()
    },
    methods: {
        async fetchToken() {
            const { data } = await axios.post('/api/token', {
                username: this.user.username
            })

            return data.token
        },
        async initializeClient(token) {
            const client = await Twilio.Chat.Client.create(token)

            client.on("tokenAboutToExpire", async () => {
                const token = await this.fetchToken()

                client.updateToken(token)
            })

            this.channel = await client.getChannelBySid('chatroom')

            this.channel.on("messageAdded", message => {
                this.messages.push(message)
            })
        },
        async fetchMessages() {
            this.messages = (await this.channel.getMessages()).items
        },
        sendMessage() {
            this.channel.sendMessage(this.newMessage)
            this.newMessage = ''
        }
    }
}
</script>
