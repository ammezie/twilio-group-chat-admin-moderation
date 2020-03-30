<template>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">Members</div>

                    <div class="card-body">
                        <ul class="list-group list-group-flush" v-if="members.length > 0">
                            <li
                                v-for="mem in members"
                                :key="mem.sid"
                                class="list-group-item d-flex justify-content-between align-items-center"
                            >
                                {{ mem.identity }}

                                <div class="btn-group">
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        @click="removeMember(mem.identity)"
                                        v-if="member.roleSid === adminRoleSid && user.username !== mem.identity"
                                    >Remove</button>

                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        @click="banMember(mem.identity)"
                                        v-if="member.roleSid === adminRoleSid && mem.roleSid === memberRoleSid && user.username !== mem.identity"
                                    >Ban</button>

                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        @click="unbanMember(mem.identity)"
                                        v-if="member.roleSid === adminRoleSid && mem.roleSid === bannedRoleSid && user.username !== mem.identity"
                                    >Unban</button>
                                </div>
                            </li>
                        </ul>
                        <p v-else>No members</p>
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
                        <div class="text-center" v-if="member.roleSid === bannedRoleSid || isBanned">
                            You have been banned from sending messages.
                        </div>

                        <input
                            type="text"
                            v-model="newMessage"
                            class="form-control"
                            placeholder="Type your message..."
                            @keyup.enter="sendMessage"
                            v-else
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
            members: [],
            messages: [],
            newMessage: '',
            channel: '',
            member: '',
            isBanned: false,
            adminRoleSid: process.env.MIX_CHANNEL_ADMIN_ROLE_SID,
            memberRoleSid: process.env.MIX_CHANNEL_MEMBER_ROLE_SID,
            bannedRoleSid: process.env.MIX_CHANNEL_BANNED_ROLE_SID
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
            this.members = await this.channel.getMembers()
            this.member = await this.channel.getMemberByIdentity(this.user.username)

            this.channel.on("messageAdded", message => {
                this.messages.push(message)
            })

            this.channel.on('memberJoined', (member) => {
                this.members.push(member)
            })

            this.channel.on('memberLeft', (member) => {
                this.members = this.members.filter((mem) => mem.sid !== member.sid)

                if (member.identity === this.user.username) {
                    window.location = '/home'
                }
            })

            this.channel.on("memberUpdated", ({ member }) => {
                if (member.identity === this.user.username && member.roleSid === this.bannedRoleSid) {
                    this.isBanned = true
                }

                if (member.identity === this.user.username && member.roleSid === this.memberRoleSid) {
                    this.isBanned = false
                }
            })
        },
        async fetchMessages() {
            this.messages = (await this.channel.getMessages()).items
        },
        sendMessage() {
            this.channel.sendMessage(this.newMessage)
            this.newMessage = ''
        },
        async removeMember(identity) {
            await this.channel.removeMember(identity)
        },
        async banMember(identity) {
            await axios.post(`/api/members/${identity}/ban`)
        },
        async unbanMember(identity) {
            await axios.post(`/api/members/${identity}/unban`)
        }
    }
}
</script>
