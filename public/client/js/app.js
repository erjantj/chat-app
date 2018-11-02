Vue.use(Api);
Vue.use(Toasted);

var app = new Vue({
    el: "#frame",
    computed: {
        auth_token: function() {
            return store.state.auth_token;
        },
        user: function() {
            return store.state.user;
        },
        contacts: function() {
            return store.state.contacts;
        },
        messages: function() {
            return store.state.messages;
        }
    },
    created() {
        document.addEventListener("beforeunload", this.logout);
    },
    methods: {
        logout: function(event) {
            this.$logout();
        }
    }
});
