Vue.component("login-input", {
    template: `
        <form id="login-input" @submit.prevent="login">
            <input v-model="username" placeholder="Enter your username" />
            <input type="Submit" />
            <span v-if="error.username">{{error.username[0]}}</span>
        </form>
    `,
    data: function() {
        return {
            username: ""
        };
    },
    computed: {
        error() {
            return store.state.loginInput.error;
        }
    },
    methods: {
        login: function() {
            var app = this;
            store.commit("setLoginInputError", {});
            this.$login({ username: this.username })
                .then(function(response) {
                    var data = response.data;
                    if (data.api_key) {
                        store.commit("setAuthToken", data.api_key);
                        store.commit("setUser", data.user);
                        store.dispatch("initWebsocket", { app: app });
                        store.commit("loadContacts");
                    } else {
                        store.commit("setAuthToken", {});
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    var data = error.response.data;
                    store.commit("setLoginInputError", data);
                })
                .then(function() {
                    // always executed
                });
        }
    }
});
