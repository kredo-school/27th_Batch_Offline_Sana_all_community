<section class="container py-4" aria-labelledby="home-community-chat-title">
    <div class="card border shadow-sm overflow-hidden home-community-chat" data-chat-url="{{ route('community.chat.index', $community->id) }}">
        <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
            <div>
                <p class="text-muted small mb-1">Community chat</p>
                <h2 id="home-community-chat-title" class="h5 mb-0">{{ $community->name }}</h2>
            </div>
            <a href="{{ route('community.show', $community->id) }}#chat" class="btn btn-outline-secondary btn-sm">Open community</a>
        </div>
        <div class="home-community-chat-list" aria-live="polite"></div>
        <div class="home-community-chat-empty">Loading messages...</div>
        <div class="home-community-chat-status" role="status" aria-live="polite"></div>
        <form action="{{ route('community.chat.store', $community->id) }}" method="POST" class="home-community-chat-form">
            @csrf
            <textarea name="message" class="form-control" rows="1" maxlength="2000" required placeholder="Write a message..." aria-label="Write a message"></textarea>
            <button type="submit" class="btn btn-primary" aria-label="Send message">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            </button>
        </form>
    </div>
</section>

<style>
    .home-community-chat { position: relative; }
    .home-community-chat-list { display: flex; flex-direction: column; gap: 8px; min-height: 180px; max-height: 420px; overflow-y: auto; margin: 0; padding: 16px; list-style: none; background: #f8f9fa; }
    .home-community-chat-message { align-self: flex-start; max-width: 80%; padding: 8px 12px; border-radius: 12px; background: #fff; overflow-wrap: anywhere; }
    .home-community-chat-message--mine { align-self: flex-end; background: #fdebdc; }
    .home-community-chat-message strong, .home-community-chat-message time { display: block; font-size: .75rem; }
    .home-community-chat-message time { color: #6c757d; }
    .home-community-chat-empty { position: absolute; inset: 78px 0 62px; display: grid; place-items: center; color: #6c757d; pointer-events: none; }
    .home-community-chat-empty[hidden] { display: none; }
    .home-community-chat-status { padding: 8px 16px 0; color: #dc3545; font-size: .82rem; }
    .home-community-chat-form { display: flex; gap: 8px; padding: 12px 16px; border-top: 1px solid #e9ecef; }
    .home-community-chat-form textarea { min-height: 40px; resize: none; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const chat = document.querySelector('.home-community-chat');
    if (!chat) return;

    const list = chat.querySelector('.home-community-chat-list');
    const empty = chat.querySelector('.home-community-chat-empty');
    const status = chat.querySelector('.home-community-chat-status');
    const form = chat.querySelector('.home-community-chat-form');
    const input = form.querySelector('textarea');
    const button = form.querySelector('button');
    let lastId = 0;
    let syncedAt = '';
    let loading = false;

    const appendMessage = (message) => {
        if (message.id <= lastId) return;
        lastId = message.id;
        const item = document.createElement('li');
        item.className = `home-community-chat-message${message.is_mine ? ' home-community-chat-message--mine' : ''}`;
        const author = document.createElement('strong');
        author.textContent = message.is_mine ? 'You' : message.sender.name;
        const body = document.createElement('div');
        body.textContent = message.body;
        const time = document.createElement('time');
        time.dateTime = message.datetime;
        time.textContent = new Date(message.datetime).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        item.append(author, body, time);
        list.append(item);
    };

    const loadMessages = async () => {
        if (loading) return;
        loading = true;
        try {
            const query = new URLSearchParams({ after: lastId, since: syncedAt });
            const response = await fetch(`${chat.dataset.chatUrl}?${query}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!response.ok) throw new Error();
            const data = await response.json();
            data.messages.forEach(appendMessage);
            syncedAt = data.synced_at;
            empty.hidden = lastId > 0;
            status.textContent = '';
            list.scrollTop = list.scrollHeight;
        } catch {
            empty.hidden = true;
            status.textContent = 'Could not load messages.';
        } finally {
            loading = false;
        }
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message) return;
        button.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ message }),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message);
            await loadMessages();
            input.value = '';
        } catch (error) {
            status.textContent = error.message || 'Could not send message.';
        } finally {
            button.disabled = false;
            input.focus();
        }
    });

    loadMessages();
    setInterval(loadMessages, 5000);
});
</script>