Vue.component("chat-content", {
    template: `
        <div class="content" v-if="currentContact.id">
            <div class="contact-profile">
                <img src="images/user-icon2.png" alt="" />
                <p>{{ currentContact.username }}</p>
            </div>
            <div class="messages" ref="messagesBlock">
                <ul>
                    <li v-for="message in messages" v-bind:class="[{ 
                        'sent': message.sender_id == currentContact.id 
                            && message.recipient_id == user.id,
                        'replies': message.recipient_id == currentContact.id 
                            && message.sender_id == user.id
                    }]">   
                        <span v-if="message.sender_id == user.id">
                            <img src="images/user-icon.png" alt="" />
                        </span>
                        <span v-if="message.sender_id == currentContact.id">
                            <img src="images/user-icon2.png" alt="" />
                        </span>
                        <p>{{message.message}}</p>
                    </li>
                </ul>
            </div>
            <div class="message-input">
                <div class="wrap">
                    <form @submit.prevent="sendMessage">
                        <input v-model="newMessage" type="text" autofocus placeholder="Write your message..." />
                        <i class="fa fa-paperclip attachment" aria-hidden="true"></i>
                        <button class="submit"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                    </form>
                </div>
            </div>
        </div>
    `,
    data: function() {
        return {
            newMessage: ""
        };
    },
    computed: {
        user: function() {
            return store.state.user;
        },
        currentContact: function() {
            return store.state.currentContact;
        },
        messages: function() {
            return store.state.messages;
        },
        expanding: function() {
            return store.state.expanding;
        }
    },
    beforeMount: function() {
        if (this.currentContact) {
            store.dispatch("loadMessages", {
                app: this,
                contact: this.currentContact
            });
        }
    },
    mounted: function() {
        store.commit("setMessageBlock", this.$refs.messagesBlock);
        store.dispatch("scrollMessageBlock");
        if (this.$refs.messagesBlock) {
            this.$refs.messagesBlock.addEventListener(
                "scroll",
                this.handleScroll
            );
        }
    },
    methods: {
        handleScroll(event) {
            if (!this.expanding && event.target.scrollTop == 0) {
                var scrollBottom =
                    event.target.scrollHeight - event.target.scrollTop;

                store.dispatch("loadMoreMessages", {
                    app: this,
                    contact: this.currentContact,
                    scrollBottom: scrollBottom
                });
            }
        },
        sendMessage: function() {
            var app = this;
            if (this.newMessage) {
                var message = {
                    sender_id: this.user.id,
                    recipient_id: this.currentContact.id,
                    message: this.newMessage
                };

                store.dispatch("sendMessage", {
                    app: app,
                    message: message
                });
            }

            store.dispatch("scrollMessageBlock");

            this.newMessage = "";
        }
    }
});
