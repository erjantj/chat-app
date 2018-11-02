var axiosApi = axios.create({
    baseURL: "http://localhost:9000/api/v1",
    timeout: 4000
});
var wsUri = "ws://localhost:8000/ws/";
var wsFormat = ["json"];
var isReconnect = true;

var Api = {
    install: function(Vue, options) {
        //private
        var myPrivateProperty = "Private property";
        var websocket = undefined;

        Vue.prototype.$initWebsocket = function() {
            websocket = new WebSocket(wsUri, wsFormat);
            websocket.onopen = function(event) {
                onOpen(event);
            };
            websocket.onclose = function(event) {
                onClose(event);
            };
            websocket.onmessage = function(event) {
                onMessage(event);
            };
            websocket.onerror = function(event) {
                onError(event);
            };
        };

        if (store.state.auth_token) {
            Vue.prototype.$initWebsocket();
        }

        function onOpen(event) {
            console.log("CONNECTED");
            var token = store.state.auth_token;
            websocket.send(
                JSON.stringify({
                    type: "auth",
                    api_key: token
                })
            );
        }

        function onClose(event) {
            console.log("DISCONNECTED");
            if (isReconnect) Vue.prototype.$initWebsocket();
        }

        function onMessage(e) {
            var payload = JSON.parse(e.data);
            if (payload.type && payload.type == "message") {
                if (
                    payload.message.sender_id == store.state.currentContact.id
                ) {
                    store.commit("appendMessage", payload.message);
                    store.dispatch("scrollMessageBlock");
                }
                var body =
                    payload.user.username + ":&nbsp " + payload.message.message;
                Vue.prototype.$toasted.show(body, {
                    theme: "primary",
                    position: "bottom-left",
                    duration: 5000
                });
            }

            if (payload.type && payload.type == "online") {
                store.commit("setOnline", payload);
            }
        }

        Vue.prototype.$login = function(data) {
            return axiosApi.post("/login", data);
        };

        Vue.prototype.$loadContacts = function() {
            var token = store.state.auth_token;
            return axiosApi.get("/user", {
                headers: { Authorization: "Bearer " + token }
            });
        };

        Vue.prototype.$loadMessages = function(contact, page) {
            var token = store.state.auth_token;
            return axiosApi.get("/message", {
                params: {
                    recipient_id: contact.id,
                    page: page
                },
                headers: { Authorization: "Bearer " + token }
            });
        };

        Vue.prototype.$sendMessage = function(message) {
            var token = store.state.auth_token;
            websocket.send(
                JSON.stringify({
                    type: "message",
                    message: message,
                    user: store.state.user,
                    api_key: token
                })
            );
        };

        Vue.prototype.$logout = function() {
            localStorage.removeItem("vuex");
        };
    }
};
