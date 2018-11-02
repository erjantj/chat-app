const vuexLocalStorage = new window.VuexPersistence.VuexPersistence({
    key: "vuex",
    storage: window.localStorage
});

const store = new Vuex.Store({
    plugins: [vuexLocalStorage.plugin],
    state: {
        auth_token: "",
        user: {},
        currentContact: {},
        currentPage: 1,
        contacts: [],
        messages: {},
        messagesBlock: null,
        expanding: false,
        loginInput: {
            error: {}
        }
    },
    mutations: {
        setUser: function(state, user) {
            this.state.user = user;
        },
        setAuthToken: function(state, token) {
            this.state.auth_token = token;
        },
        setContacts: function(state, contacts) {
            this.state.contacts = contacts;
        },
        setCurrentContact: function(state, contact) {
            this.state.currentContact = contact;
        },
        setMessages: function(state, messages) {
            this.state.messages = messages;
        },
        setMoreMessages: function(state, messages) {
            this.state.messages = messages.concat(this.state.messages);
        },
        setLoginInputError: function(state, error) {
            this.state.loginInput.error = error;
        },
        appendMessage: function(state, message) {
            this.state.messages.push(message);
        },
        setOnline: function(state, { user, is_online }) {
            var contacts = this.state.contacts;
            var user_exists = false;
            for (i = 0; i < contacts.length; i++) {
                if (contacts[i].id == user.id) {
                    contacts[i].is_online = is_online;
                    user_exists = true;
                    break;
                }
            }
            if (!user_exists) {
                contacts.push(user);
            }
            this.state.contacts = contacts;
        },
        setMessageBlock: function(state, messagesBlock) {
            this.state.messagesBlock = messagesBlock;
        },
        setCurrentPage: function(state, page) {
            this.state.currentPage = page;
        },
        setExpanding: function(state, expanding) {
            this.state.expanding = expanding;
        }
    },
    actions: {
        loadContacts: function(state, app) {
            state.commit("setMessages", []);
            app.$loadContacts()
                .then(function(response) {
                    state.commit("setContacts", response.data);
                })
                .catch(function(error) {
                    state.commit("setContacts", []);
                })
                .then(function() {
                    // always executed
                });
        },
        loadMessages: function(state, { app, contact }) {
            state.commit("setCurrentContact", contact);
            state.commit("setCurrentPage", 1);
            app.$loadMessages(contact, state.state.currentPage)
                .then(function(response) {
                    if (response.data.data) {
                        state.commit("setMessages", response.data.data);
                        state.dispatch("scrollMessageBlock");
                    }
                })
                .catch(function(error) {
                    if (error.response.status == 401) {
                        app.$logout();
                    }
                })
                .then(function() {
                    // always executed
                });
        },
        loadMoreMessages: function(state, { app, contact, scrollBottom }) {
            state.commit("setCurrentPage", state.state.currentPage + 1);
            state.commit("setExpanding", true);
            app.$loadMessages(contact, state.state.currentPage)
                .then(function(response) {
                    if (response.data.data && response.data.data.length > 0) {
                        state.commit("setMoreMessages", response.data.data);
                        var container = state.state.messagesBlock;
                        setTimeout(function() {
                            container.scrollTop =
                                container.scrollHeight - scrollBottom;
                        }, 3);
                        state.commit("setExpanding", false);
                    }
                })
                .catch(function(error) {
                    if (error.response.status == 401) {
                        app.$logout();
                    }
                })
                .then(function() {
                    state.commit("setExpanding", false);
                });
        },
        sendMessage: function(state, { app, message }) {
            state.commit("appendMessage", message);
            app.$sendMessage(message);
        },
        initWebsocket: function(state, { app }) {
            app.$initWebsocket();
        },
        scrollMessageBlock: function(store) {
            var container = store.state.messagesBlock;
            setTimeout(function() {
                container.scrollTop = container.scrollHeight;
            }, 10);
        }
    }
});
