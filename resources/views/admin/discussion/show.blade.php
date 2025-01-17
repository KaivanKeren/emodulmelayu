@extends('layouts.admin') <!-- sesuaikan dengan layout Anda -->

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-6">{{ $discussion->title }}</h1>

        <div class="space-y-6">
            @foreach ($messages as $message)
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <div class="flex items-center space-x-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($message->user->name) }}" alt="Avatar"
                            class="w-10 h-10 rounded-full">
                        <div class="flex-1">
                            <div class="font-semibold text-lg">{{ $message->user->name }}</div>
                            <div class="text-gray-500 text-sm">{{ $message->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div class="mt-2 text-gray-800">
                        {{ $message->content }}
                    </div>

                    @if ($message->replies->isNotEmpty())
                        <div class="mt-4 space-y-4 pl-10 border-l-2 border-gray-200">
                            @foreach ($message->replies as $reply)
                                <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                                    <div class="flex items-center space-x-3">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($reply->user->name) }}"
                                            alt="Avatar" class="w-10 h-10 rounded-full">
                                        <div class="flex-1">
                                            <div class="font-semibold text-lg">{{ $reply->user->name }}</div>
                                            <div class="text-gray-500 text-sm">{{ $reply->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-gray-800">
                                        {{ $reply->content }}
                                    </div>

                                    @if ($reply->replies->isNotEmpty())
                                        <div class="mt-4 space-y-4 pl-10 border-l-2 border-gray-200">
                                            @foreach ($reply->replies as $nestedReply)
                                                <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                                                    <div class="flex items-center space-x-3">
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($nestedReply->user->name) }}"
                                                            alt="Avatar" class="w-10 h-10 rounded-full">
                                                        <div class="flex-1">
                                                            <div class="font-semibold text-lg">
                                                                {{ $nestedReply->user->name }}</div>
                                                            <div class="text-gray-500 text-sm">
                                                                {{ $nestedReply->created_at->diffForHumans() }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 text-gray-800">
                                                        {{ $nestedReply->content }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        <!-- Message Input Form -->
        <div class="mt-6">
            <form action="#" method="POST" class="bg-white p-4 rounded-lg shadow-md">
                @csrf
                <div class="flex items-center space-x-4">
                    <img src="https://ui-avatars.com/api/{{ urlencode(Auth::user()->name) }}" alt="Avatar" class="w-10 h-10 rounded-full">
                    <div class="flex-1">
                        <textarea name="message" id="message" rows="4" class="w-full p-2 border rounded-lg"
                            placeholder="Type your message..."></textarea>
                    </div>
                </div>
                <div class="mt-4 text-right">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">Send
                        Message</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Setup Pusher
        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
        });

        const channel = pusher.subscribe('discussion.{{ $discussion->id }}');
        channel.bind('NewMessageEvent', function(data) {
            console.log('New message:', event);
            alert('New message:', data)
        });
    </script>
@endsection
