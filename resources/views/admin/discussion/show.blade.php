{{-- resources/views/discussions/show.blade.php --}}
@extends('layouts.admin')

@section('title', $discussion->title)

@push('scripts')
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        const discussionId = {{ $discussion->id }};
        const userId = {{ auth()->id() ?? 'null' }};

        // Initialize Pusher with debug
        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            encrypted: true,
            logToConsole: true // Debugging aktif
        });


        // Subscribe to the channel
        const channel = pusher.subscribe('discussion.' + discussionId);

        // Debug connection
        pusher.connection.bind('connected', function() {
            console.log('Connected to Pusher');
        });

        pusher.connection.bind('error', function(err) {
            console.log('Pusher connection error:', err);
        });

        // Bind to event
        channel.bind('new-message', function(data) {
            console.log('Received message:', data);
            if (data.message.user_id !== userId) {
                addMessage(data.message);
            }
        });

        function addMessage(message) {
            console.log('Adding message:', message);
            const messageContainer = message.parent_id ?
                document.querySelector(`#replies-${message.parent_id}`) :
                document.querySelector('#messages-container');

            if (!messageContainer) {
                console.error('Message container not found');
                return;
            }

            const template = document.querySelector('#message-template').content.cloneNode(true);

            // Update template
            const messageDiv = template.querySelector('.message');
            messageDiv.id = `message-${message.id}`;

            template.querySelector('.user-name').textContent = message.user.name;
            template.querySelector('.message-time').textContent = new Date(message.created_at).toLocaleString();
            template.querySelector('.message-content').textContent = message.content;

            if (message.user_id === userId) {
                const actions = template.querySelector('.message-actions');
                if (actions) {
                    actions.style.display = 'flex';
                }
            }

            messageContainer.appendChild(template);
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }

        function sendMessage(form) {
            event.preventDefault(); // Tambahkan ini untuk mencegah form submit default

            const content = form.querySelector('textarea[name="content"]').value;
            const parentId = form.querySelector('input[name="parent_id"]')?.value;

            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        content,
                        parent_id: parentId,
                        _token: '{{ csrf_token() }}' // Tambahkan CSRF token
                    })
                })
                .then(response => response.json())
                .then(message => {
                    addMessage(message);
                    form.reset();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal mengirim pesan. Silakan coba lagi.');
                });

            return false;
        }


        function showReplyForm(messageId) {
            const replyForm = document.querySelector(`#reply-form-${messageId}`);
            replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
        }

        function deleteMessage(messageId) {
            if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
                fetch(`/messages/${messageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(() => {
                    document.querySelector(`#message-${messageId}`).remove();
                });
            }
        }
    </script>

    <template id="message-template">
        <div class="message bg-white p-4 rounded-lg shadow mb-4">
            <div class="flex justify-between items-start">
                <div>
                    <span class="user-name font-semibold"></span>
                    <span class="message-time text-sm text-gray-500 ml-2"></span>
                </div>
                <div class="message-actions hidden space-x-2">
                    <button onclick="showReplyForm(messageId)" class="text-sm text-blue-600 hover:text-blue-800">
                        Reply
                    </button>
                    <button onclick="deleteMessage(messageId)" class="text-sm text-red-600 hover:text-red-800">
                        Delete
                    </button>
                </div>
            </div>
            <p class="message-content mt-2 text-gray-700"></p>
            <div class="replies-container mt-4 pl-4 border-l-2 border-gray-200"></div>
        </div>
    </template>
@endpush

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="mb-6">
                        <a href="{{ route('discussions.index') }}"
                            class="text-indigo-600 hover:text-indigo-800 hover:underline">
                            &larr; Kembali ke Daftar Diskusi
                        </a>
                    </div>

                    <div class="border-b border-gray-200 pb-4">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $discussion->title }}</h1>
                        <div class="mt-2 text-sm text-gray-500">
                            <span>Oleh {{ $discussion->user->name }}</span>
                            <span class="mx-2">&bull;</span>
                            <span>{{ $discussion->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>

                    <div class="mt-6 prose max-w-none">
                        {!! nl2br(e($discussion->content)) !!}
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4">Diskusi</h3>

                        <div id="messages-container" class="space-y-4 mb-4 max-h-[500px] overflow-y-auto">
                            @foreach ($discussion->messages()->whereNull('parent_id')->latest()->get() as $message)
                                <div id="message-{{ $message->id }}" class="bg-white p-4 rounded-lg shadow">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="font-semibold">{{ $message->user->name }}</span>
                                            <span class="text-sm text-gray-500 ml-2">
                                                {{ $message->created_at->format('d M Y H:i') }}
                                            </span>
                                        </div>
                                        @if (auth()->id() === $message->user_id)
                                            <div class="flex space-x-2">
                                                <button onclick="showReplyForm({{ $message->id }})"
                                                    class="text-sm text-blue-600 hover:text-blue-800">
                                                    Reply
                                                </button>
                                                <button onclick="deleteMessage({{ $message->id }})"
                                                    class="text-sm text-red-600 hover:text-red-800">
                                                    Delete
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    <p class="mt-2 text-gray-700">{{ $message->content }}</p>

                                    {{-- Reply Form --}}
                                    <div id="reply-form-{{ $message->id }}" class="mt-4 hidden">
                                        <form onsubmit="return sendMessage(this)"
                                            action="{{ route('discussions.messages.store', $discussion) }}"
                                            {{-- action="#"  --}} class="space-y-4">
                                            <input type="hidden" name="parent_id" value="{{ $message->id }}">
                                            <textarea name="content"
                                                class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                placeholder="Tulis balasan..." rows="2"></textarea>
                                            <button type="submit"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                                Balas
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Replies Container --}}
                                    <div id="reply-form-{{ $message->id }}" class="mt-4 hidden">
                                        <form onsubmit="sendMessage(this); return false;"
                                            action="{{ route('discussions.messages.store', $discussion) }}" method="POST"
                                            class="space-y-4">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $message->id }}">
                                            <textarea name="content"
                                                class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                placeholder="Tulis balasan..." rows="2" required></textarea>
                                            <button type="submit"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                                Balas
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @auth
                            <form onsubmit="sendMessage(this); return false;"
                                action="{{ route('discussions.messages.store', $discussion) }}" method="POST" class="mt-4">
                                @csrf
                                <div class="flex space-x-4">
                                    <textarea name="content"
                                        class="flex-1 rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        placeholder="Tulis pesan..." rows="2" required></textarea>
                                    <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                        Kirim
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="mt-4 p-4 bg-gray-100 rounded-lg text-center">
                                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800">
                                    Login untuk bergabung dalam diskusi
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
