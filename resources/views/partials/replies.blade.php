@foreach ($replies->sortBy('created_at') as $reply)
    <div class="p-6 bg-gray-50" id="message-{{ $reply->id }}">
        <div class="flex items-start space-x-4">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($reply->user->name) }}" alt="{{ $reply->user->name }}"
                class="w-10 h-10 rounded-full ring-2 ring-gray-100">

            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $reply->user->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button onclick="showReplyForm({{ $reply->id }})"
                            class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                            Reply
                        </button>

                        @if ($reply->user_id === Auth::id())
                            <button onclick="deleteMessage({{ $reply->id }})"
                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 transition-colors">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        @endif
                    </div>
                </div>

                <div class="mt-2 text-sm text-gray-700 leading-relaxed">
                    {{ $reply->content }}
                </div>
            </div>
        </div>

        <div id="reply-form-{{ $reply->id }}" class="hidden mt-4 pl-14">
            <form onsubmit="handleReply(event, {{ $reply->id }})" class="space-y-3">
                <textarea name="message" rows="2"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition"
                    placeholder="Write your reply..."></textarea>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideReplyForm({{ $reply->id }})"
                        class="px-3 py-1.5 text-xs font-medium text-gray-700 hover:text-gray-800 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        Reply
                    </button>
                </div>
            </form>
        </div>

        <div class="replies-container-{{ $reply->id }} mt-4 pl-14">
            @if ($reply->replies)
                @include('partials.replies', ['replies' => $reply->replies])
            @endif
        </div>
    </div>
@endforeach
