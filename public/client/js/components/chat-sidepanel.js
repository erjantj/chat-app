Vue.component("chat-sidepanel", {
    template: `
        <div id="sidepanel">
            <div id="profile">
                <div class="wrap">
                    <img id="profile-img" src="images/user-icon.png" class="online" alt="" />
                    <p>{{user.username}}</p>
                </div>
            </div>
            <div id="search">
                <label for=""><i class="fa fa-search" aria-hidden="true"></i></label>
                <input type="text" placeholder="Search contacts..." />
            </div>
            <div id="contacts">
                <ul>
                    <li class="contact" v-for="contact in contacts" v-on:click="selectContact(contact)" v-bind:class="[{ 'active' : contact.id == currentContact.id }]">
                        <div class="wrap">
                            <span class="contact-status" v-bind:class="[{ 'online' : contact.is_online==1, 'offline' : contact.is_online==0 }]"></span>
                            <img src="images/user-icon2.png" alt="" />
                            <div class="meta">
                                <p class="name">{{contact.username}}</p>
                                <p class="preview"></p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    `,
    data: function() {},
    computed: {
        user: function() {
            return store.state.user;
        },
        contacts: function() {
            return store.state.contacts;
        },
        currentContact: function() {
            return store.state.currentContact;
        }
    },
    beforeMount() {
        store.dispatch("loadContacts", this);
    },
    methods: {
        selectContact: function(contact) {
            store.dispatch("loadMessages", { app: this, contact: contact });
        }
    }
});
