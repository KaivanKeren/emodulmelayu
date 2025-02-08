@extends('layouts.admin')

@section('title', $discussion->title)

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="p-4 sm:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $discussion->title }}</h2>
                    <p class="mt-2 text-sm text-gray-500">Dibuat oleh {{ $discussion->user->name }} •
                        {{ $discussion->created_at->format('d M Y') }}</p>
                </div>
                <a href="{{ route('discussions.index') }}"
                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
            <div class="mt-4 prose prose-blue max-w-none">
                <p class="text-gray-600 text-lg leading-relaxed">{{ $discussion->description }}</p>
            </div>
        </div>

        <div class="space-y-8" id="messages-container">
            @foreach ($messages->sortBy('created_at') as $message)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                    id="message-{{ $message->id }}">
                    <div class="p-6">
                        <div class="flex items-start space-x-4">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($message->user->name) }}"
                                alt="{{ $message->user->name }}" class="w-12 h-12 rounded-full ring-2 ring-gray-100">

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-base font-semibold text-gray-900">{{ $message->user->name }}</h2>
                                        <p class="text-sm text-gray-500">{{ $message->created_at->diffForHumans() }}</p>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <button onclick="showReplyForm({{ $message->id }})"
                                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                            Reply
                                        </button>

                                        @if ($message->user_id === Auth::id())
                                            <button onclick="deleteMessage({{ $message->id }})"
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-700 transition-colors">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 text-gray-700 leading-relaxed">
                                    {{ $message->content }}
                                </div>
                            </div>
                        </div>

                        <div id="reply-form-{{ $message->id }}" class="hidden mt-6 pl-16">
                            <form onsubmit="handleReply(event, {{ $message->id }})" class="space-y-4">
                                <textarea name="message" rows="3"
                                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition"
                                    placeholder="Write your reply..."></textarea>
                                <div class="flex justify-end space-x-3">
                                    <button type="button" onclick="hideReplyForm({{ $message->id }})"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-800 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                        Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="replies-container-{{ $message->id }} border-t border-gray-100">
                        @include('partials.replies', ['replies' => $message->replies])
                    </div>
                </div>
            @endforeach
        </div>

        <!-- New Message Form -->
        <div class="mt-8">
            <form onsubmit="handleNewMessage(event)" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                @csrf
                <div class="flex items-start space-x-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}"
                        alt="{{ Auth::user()->name }}" class="w-12 h-12 rounded-full ring-2 ring-gray-100">

                    <div class="flex-1 min-w-0">
                        <!-- Error Message Display -->
                        <div class="error-container hidden mb-3">
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                                <p class="text-sm error-message"></p>
                            </div>
                        </div>
                        <textarea name="message" rows="4"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition"
                            placeholder="Ketik pesan..."></textarea>

                        <div class="mt-4 flex justify-end">
                            <button type="submit"
                                class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                Send Message
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const discussionId = {{ $discussion->id }}

        function showReplyForm(messageId) {
            document.getElementById(`reply-form-${messageId}`).classList.remove('hidden');
        }

        function hideReplyForm(messageId) {
            document.getElementById(`reply-form-${messageId}`).classList.add('hidden');
        }

        async function handleNewMessage(event) {
            event.preventDefault();
            const form = event.target;
            const message = form.querySelector('textarea[name="message"]').value;
            const token = document.querySelector('meta[name="csrf-token"]').content;

            try {
                const response = await fetch(`/admin/discussions/${discussionId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json', // Add this to ensure JSON response
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        message: message
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to send message');
                }

                const result = await response.json();

                console.log('API Response:', result);

                if (result.code === 200) {
                    const messagesContainer = document.getElementById('messages-container');
                    console.log('Message data:', result.data[0]); // Add this line to see the message data
                    messagesContainer.innerHTML += createMessageHTML(result.data[0]);

                    form.reset();

                } else {
                    alert(result.message || 'Failed to send message');
                    window.location.reload()
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while sending the message');
            }
        }

        async function handleReply(event, parentId) {
            event.preventDefault();
            const form = event.target;
            const message = form.querySelector('textarea[name="message"]').value;
            const token = document.querySelector('meta[name="csrf-token"]').content;

            try {
                const response = await fetch(`/admin/discussions/${discussionId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json', // Add this to ensure JSON response
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        message: message,
                        reply: parentId
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to send reply');
                }

                const result = await response.json();

                if (result.code === 200) {
                    // Append the new reply to the replies container
                    const repliesContainer = document.querySelector(`.replies-container-${parentId}`);
                    repliesContainer.innerHTML += createMessageHTML(result.data[0]);

                    // Clear and hide the form
                    form.reset();
                    hideReplyForm(parentId);
                } else {
                    alert(result.message || 'Failed to send reply');
                    window.location.reload()
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while sending the reply');
            }
        }

        async function deleteMessage(messageId) {
            if (!confirm('Are you sure you want to delete this message?')) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]').content;

            try {
                const response = await fetch(`/admin/discussions/${discussionId}/messages/${messageId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to delete message');
                }

                const result = await response.json();

                if (result.code === 200) {
                    // Remove the message element from the DOM
                    const messageElement = document.getElementById(`message-${messageId}`);
                    messageElement.remove();
                } else {
                    alert(result.message || 'Failed to delete message');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while deleting the message');
            }
        }

        function createMessageHTML(message) {
            if (!message || !message.id) {
                console.error('Invalid message data:', message);
                window.location.reload()
                return '';
            }

            const userName = message.user?.name || 'Unknown User';
            const content = message.content || '';
            const createdAt = message.created_at || new Date().toISOString();
            const currentUserId = parseInt(document.querySelector('meta[name="user-id"]').content);
            const deleteButton = message.user_id === currentUserId ?
                `<button onclick="deleteMessage(${message.id})" class="text-red-500 hover:text-red-600">Delete</button>` :
                '';

            return `
        <div class="bg-white p-4 rounded-lg shadow-md" id="message-${message.id}">
            <div class="flex items-center space-x-3">
                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}" alt="Avatar"
                    class="w-10 h-10 rounded-full">
                <div class="flex-1">
                    <div class="font-semibold text-lg">${userName}</div>
                    <div class="text-gray-500 text-sm">${createdAt}</div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="showReplyForm(${message.id})" class="text-blue-500 hover:text-blue-600">Reply</button>
                    ${deleteButton}
                </div>
            </div>
            <div class="mt-2 text-gray-800">
                ${content}
            </div>
            <div id="reply-form-${message.id}" class="hidden mt-4">
                <form onsubmit="handleReply(event, ${message.id})" class="space-y-4">
                    <textarea name="message" rows="3" class="w-full p-2 border rounded-lg" placeholder="Type your reply..."></textarea>
                    <div class="text-right">
                        <button type="button" onclick="hideReplyForm(${message.id})" class="mr-2 text-gray-500">Cancel</button>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Reply</button>
                    </div>
                </form>
            </div>
            <div class="replies-container-${message.id} mt-4 space-y-4 pl-10 border-l-2 border-gray-200"></div>
        </div>
    `;
        }

        function showError(container, message) {
            const errorContainer = container.querySelector('.error-container');
            const errorMessage = errorContainer.querySelector('.error-message');
            errorContainer.classList.remove('hidden');
            errorMessage.textContent = message;
        }

        function hideError(container) {
            const errorContainer = container.querySelector('.error-container');
            if (errorContainer) {
                errorContainer.classList.add('hidden');
                const errorMessage = errorContainer.querySelector('.error-message');
                errorMessage.textContent = '';
            }
        }
    </script>
@endsection
