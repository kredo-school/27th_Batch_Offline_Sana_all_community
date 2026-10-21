import './bootstrap';

const chatPanel = document.querySelector('#message-chat-panel');
const chatTitle = document.querySelector('#message-chat-title');
const chatStatus = document.querySelector('#message-chat-status');
const chatList = document.querySelector('#message-chat-list');
const chatForm = document.querySelector('#message-chat-form');
const chatInput = document.querySelector('#message-chat-input');

if (chatPanel && chatTitle && chatStatus && chatList && chatForm && chatInput) {
	let activeRequest = 0;

	const appendMessage = (message) => {
		const item = document.createElement('li');
		item.className = message.is_mine
			? 'message-chat-item message-chat-item--mine'
			: 'message-chat-item';
		const sender = message.sender || {};
		const senderName = sender.username || sender.name || 'Unknown user';
		const senderHeader = document.createElement('div');
		senderHeader.className = 'message-chat-sender';

		const avatar = document.createElement('span');
		avatar.className = 'message-chat-avatar';
		if (sender.avatar) {
			const image = document.createElement('img');
			image.src = sender.avatar;
			image.alt = `${senderName} avatar`;
			avatar.append(image);
		} else {
			avatar.textContent = Array.from(sender.name || senderName)[0]?.toLocaleUpperCase() || '?';
		}

		const username = document.createElement('span');
		username.textContent = senderName;
		senderHeader.append(avatar, username);
		item.append(senderHeader);

		const body = document.createElement('p');
		body.textContent = message.body;
		item.append(body);

		if (message.sent_at) {
			const time = document.createElement('time');
			time.textContent = message.sent_at;
			if (message.datetime) {
				time.dateTime = message.datetime;
			}
			item.append(time);
		}

		chatList.append(item);
		chatList.scrollTop = chatList.scrollHeight;
	};

	document.addEventListener('click', async (event) => {
		const openButton = event.target.closest('[data-message-chat-url]');

		if (openButton) {
			const requestId = ++activeRequest;
			chatPanel.hidden = false;
			chatTitle.textContent = openButton.dataset.messageChatName || 'Conversation';
			chatStatus.textContent = 'Loading messages...';
			chatList.replaceChildren();
			chatForm.dataset.sendUrl = openButton.dataset.messageChatSendUrl;

			try {
				const response = await fetch(openButton.dataset.messageChatUrl, {
					headers: { Accept: 'application/json' },
					credentials: 'same-origin',
				});

				if (!response.ok) {
					throw new Error('Unable to load conversation');
				}

				const conversation = await response.json();
				if (requestId !== activeRequest) {
					return;
				}

				chatTitle.textContent = conversation.participant.name || chatTitle.textContent;
				chatStatus.textContent = conversation.messages.length ? '' : 'No messages in this conversation.';

				for (const message of conversation.messages) {
					appendMessage(message);
				}
			} catch {
				if (requestId === activeRequest) {
					chatStatus.textContent = 'Could not load messages.';
				}
			}

			return;
		}

		if (event.target.closest('[data-close-message-chat]')) {
			activeRequest += 1;
			chatPanel.hidden = true;
		}
	});

	chatForm.addEventListener('submit', async (event) => {
		event.preventDefault();
		const message = chatInput.value.trim();
		if (!message || !chatForm.dataset.sendUrl) {
			return;
		}

		const submitButton = chatForm.querySelector('button[type="submit"]');
		submitButton.disabled = true;
		chatStatus.textContent = 'Sending...';

		try {
			const response = await fetch(chatForm.dataset.sendUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
					'X-Requested-With': 'XMLHttpRequest',
				},
				body: JSON.stringify({ message }),
			});
			const result = await response.json();

			if (!response.ok) {
				throw new Error(result.message || 'Could not send message.');
			}

			appendMessage(result.message);
			chatInput.value = '';
			chatStatus.textContent = '';
		} catch (error) {
			chatStatus.textContent = error.message || 'Could not send message.';
		} finally {
			submitButton.disabled = false;
			chatInput.focus();
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.target.matches('.message-list-row') && ['Enter', ' '].includes(event.key)) {
			event.preventDefault();
			event.target.click();
			return;
		}

		if (event.key === 'Escape') {
			activeRequest += 1;
			chatPanel.hidden = true;
		}
	});
}
