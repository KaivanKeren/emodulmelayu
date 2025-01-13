import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '8cc5f2281658855ce0bb',
    cluster: 'ap1',
    encrypted: true,
    useTLS: true
});
