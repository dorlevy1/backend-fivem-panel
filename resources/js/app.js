import './bootstrap';

import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;
window.io = require('socket.io-client');

window.Echo = new Echo({
    broadcaster : 'socket.io',
    host : window.location.hostname + ':3000',
    authEndpoint : '/broadcasting/auth'
});

// window.Echo = new Echo({
//     broadcaster : 'pusher',
//     key : process.env.MIX_PUSHER_APP_KEY,
//     cluster : process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted : true,
// });
//