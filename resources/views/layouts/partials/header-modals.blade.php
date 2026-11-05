{{-- ヘッダーのメッセージ機能などで使うモーダルとチャット欄 --}}
    <div class="modal fade" id="action-confirmation-modal" tabindex="-1"
        aria-labelledby="action-confirmation-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="action-confirmation-title">Confirm action</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="action-confirmation-message"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-warning" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="action-confirmation-submit">Continue</button>
                </div>
            </div>
        </div>
    </div>

    @auth
        <div class="modal fade" id="create-message-modal" tabindex="-1"
            aria-labelledby="create-message-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="create-message-title">Create Message</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        @foreach ([['title' => 'Following', 'users' => $followedMessageRecipients], ['title' => 'Others', 'users' => $otherMessageRecipients]] as $recipientGroup)
                            <section class="border-bottom last:border-bottom-0">
                                <h3 class="px-3 py-2 mb-0 bg-light text-uppercase small fw-bold text-secondary">{{ $recipientGroup['title'] }}</h3>
                                <div class="list-group list-group-flush">
                                    @forelse ($recipientGroup['users'] as $recipient)
                                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3"
                                            data-bs-dismiss="modal"
                                            data-message-chat-url="{{ route('messages.conversation', $recipient->id) }}"
                                            data-message-chat-send-url="{{ route('messages.send', $recipient->id) }}"
                                            data-message-chat-name="{{ $recipient->username ?? $recipient->name }}">
                                            <span class="message-chat-avatar">
                                                @if ($recipient->profile?->avatar)
                                                    <img src="{{ $recipient->profile->avatar }}" alt="{{ $recipient->name }}">
                                                @else
                                                    {{ strtoupper(substr($recipient->name ?? '?', 0, 1)) }}
                                                @endif
                                            </span>
                                            <span class="text-start">
                                                <strong class="d-block">{{ $recipient->username ?? $recipient->name }}</strong>
                                                @if ($recipient->username && $recipient->name !== $recipient->username)
                                                    <small class="text-muted">{{ $recipient->name }}</small>
                                                @endif
                                            </span>
                                        </button>
                                    @empty
                                        <p class="text-muted text-center my-3">No users in this group.</p>
                                    @endforelse
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <aside id="message-chat-panel" class="message-chat-panel" role="dialog" aria-labelledby="message-chat-title" hidden>
            <header class="message-chat-header">
                <h2 id="message-chat-title">{{ __('Conversation') }}</h2>
                <button type="button" class="message-chat-close" data-close-message-chat aria-label="{{ __('Close conversation') }}">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </header>
            <div id="message-chat-status" class="message-chat-status" role="status" aria-live="polite"></div>
            <ol id="message-chat-list" class="message-chat-list" aria-live="polite"></ol>
            <form id="message-chat-form" class="message-chat-form">
                <textarea id="message-chat-input" class="form-control" rows="1" maxlength="5000"
                    placeholder="{{ __('Write a message') }}" aria-label="{{ __('Write a message') }}" required></textarea>
                <button type="submit" class="btn btn-success" aria-label="{{ __('Send message') }}">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                </button>
            </form>
        </aside>
    @endauth
