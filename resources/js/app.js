import './bootstrap';
import './search-suggest';
import { Modal } from 'bootstrap';

const confirmationModalElement = document.querySelector('#action-confirmation-modal');
const confirmationMessage = document.querySelector('#action-confirmation-message');
const confirmationSubmit = document.querySelector('#action-confirmation-submit');

if (confirmationModalElement && confirmationMessage && confirmationSubmit) {
	const confirmationModal = new Modal(confirmationModalElement);
	let pendingForm = null;
	let confirmedForm = null;

	document.addEventListener('submit', (event) => {
		const form = event.target.closest('form[data-confirm]');
		if (!form) {
			return;
		}

		if (confirmedForm === form) {
			confirmedForm = null;
			return;
		}

		event.preventDefault();
		event.stopPropagation();
		pendingForm = form;
		const confirmMessage = form.dataset.confirmMessage || confirmationSubmit.dataset.continueLabel || 'Continue';
		const isDeleteAction = Boolean(form.dataset.confirmAction)
			|| /\b(delete|remove)\b/i.test(confirmMessage);
		const deleteLabel = confirmationSubmit.dataset.deleteLabel || form.dataset.confirmAction || 'Delete';
		const continueLabel = confirmationSubmit.dataset.continueLabel || 'Continue';
		confirmationMessage.textContent = confirmMessage;
		confirmationSubmit.textContent = isDeleteAction ? (form.dataset.confirmAction || deleteLabel) : continueLabel;
		confirmationSubmit.classList.toggle('btn-danger', isDeleteAction);
		confirmationSubmit.classList.toggle('btn-primary', !isDeleteAction);
		confirmationModal.show();
	}, true);

	confirmationSubmit.addEventListener('click', () => {
		if (!pendingForm) {
			return;
		}

		const form = pendingForm;
		pendingForm = null;
		if (!form.reportValidity()) {
			return;
		}

		confirmedForm = form;
		confirmationModal.hide();
		form.requestSubmit();
	});
}

const chatPanel = document.querySelector('#message-chat-panel');
const chatTitle = document.querySelector('#message-chat-title');
const chatStatus = document.querySelector('#message-chat-status');
const chatList = document.querySelector('#message-chat-list');
const chatForm = document.querySelector('#message-chat-form');
const chatInput = document.querySelector('#message-chat-input');

if (chatPanel && chatTitle && chatStatus && chatList && chatForm && chatInput) {
	let activeRequest = 0;
	let dragState = null;

	const chatHeader = chatPanel.querySelector('.message-chat-header');
	const keepPanelInViewport = (left, top) => {
		const maxLeft = Math.max(8, window.innerWidth - chatPanel.offsetWidth - 8);
		const maxTop = Math.max(8, window.innerHeight - chatPanel.offsetHeight - 8);
		return {
			left: Math.min(Math.max(8, left), maxLeft),
			top: Math.min(Math.max(8, top), maxTop),
		};
	};

	if (chatHeader) {
		chatHeader.addEventListener('pointerdown', (event) => {
			if (event.button !== 0 || event.target.closest('button')) {
				return;
			}

			const bounds = chatPanel.getBoundingClientRect();
			dragState = {
				offsetX: event.clientX - bounds.left,
				offsetY: event.clientY - bounds.top,
			};
			chatPanel.style.left = `${bounds.left}px`;
			chatPanel.style.top = `${bounds.top}px`;
			chatPanel.style.right = 'auto';
			chatPanel.style.bottom = 'auto';
			chatHeader.setPointerCapture(event.pointerId);
		});

		chatHeader.addEventListener('pointermove', (event) => {
			if (!dragState) {
				return;
			}

			const position = keepPanelInViewport(
				event.clientX - dragState.offsetX,
				event.clientY - dragState.offsetY,
			);
			chatPanel.style.left = `${position.left}px`;
			chatPanel.style.top = `${position.top}px`;
		});

		const stopDragging = (event) => {
			if (!dragState) {
				return;
			}

			dragState = null;
			if (chatHeader.hasPointerCapture(event.pointerId)) {
				chatHeader.releasePointerCapture(event.pointerId);
			}
		};

		chatHeader.addEventListener('pointerup', stopDragging);
		chatHeader.addEventListener('pointercancel', stopDragging);
	}

	window.addEventListener('resize', () => {
		if (chatPanel.hidden || chatPanel.style.left === '') {
			return;
		}

		const bounds = chatPanel.getBoundingClientRect();
		const position = keepPanelInViewport(bounds.left, bounds.top);
		chatPanel.style.left = `${position.left}px`;
		chatPanel.style.top = `${position.top}px`;
	});

	const requestHeaders = {
		Accept: 'application/json',
		'Content-Type': 'application/json',
		'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
		'X-Requested-With': 'XMLHttpRequest',
	};

	const appendMessage = (message) => {
		const item = document.createElement('li');
		item.dataset.messageId = message.id;
		item.dataset.updateUrl = message.update_url;
		item.dataset.deleteUrl = message.delete_url;
		item.className = message.is_mine
			? 'message-chat-item message-chat-item--mine'
			: 'message-chat-item';
		const bubble = document.createElement('div');
		bubble.className = 'message-chat-bubble';
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
		bubble.append(senderHeader);

		const body = document.createElement('p');
		body.textContent = message.body;
		bubble.append(body);

		if (message.body) {
			const translation = document.createElement('p');
			translation.className = 'message-chat-translation';
			translation.hidden = true;
			bubble.append(translation);
		}

		if (message.sent_at) {
			const time = document.createElement('time');
			time.textContent = message.sent_at;
			if (message.datetime) {
				time.dateTime = message.datetime;
			}
			bubble.append(time);
		}

		item.append(bubble);

		if (message.body) {
			const actions = document.createElement('div');
			actions.className = 'message-chat-actions';
			const translateButton = document.createElement('button');
			translateButton.type = 'button';
			translateButton.dataset.messageAction = 'translate';
			translateButton.dataset.translateUrl = message.translate_url;
			translateButton.setAttribute('aria-label', chatPanel.dataset.translateLabel);
			translateButton.title = chatPanel.dataset.translateLabel;
			translateButton.innerHTML = '<i class="fa-solid fa-language" aria-hidden="true"></i>';
			actions.append(translateButton);

			if (message.is_mine) {
			const editButton = document.createElement('button');
			editButton.type = 'button';
			editButton.dataset.messageAction = 'edit';
			editButton.setAttribute('aria-label', 'Edit message');
			editButton.title = 'Edit message';
			editButton.innerHTML = '<i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>';
			const deleteButton = document.createElement('button');
			deleteButton.type = 'button';
			deleteButton.dataset.messageAction = 'delete';
			deleteButton.setAttribute('aria-label', 'Delete message');
			deleteButton.title = 'Delete message';
			deleteButton.innerHTML = '<i class="fa-solid fa-trash" aria-hidden="true"></i>';
			actions.append(editButton, deleteButton);
			}
			bubble.append(actions);
		}

		chatList.append(item);
		chatList.scrollTop = chatList.scrollHeight;
	};

	const replaceMessage = (item, message) => {
		const currentScroll = chatList.scrollTop;
		item.remove();
		appendMessage(message);
		chatList.scrollTop = currentScroll;
	};

	document.addEventListener('click', async (event) => {
		if (event.target.closest('form')) {
			return;
		}

		const openButton = event.target.closest('[data-message-chat-url]');

		if (openButton) {
			const requestId = ++activeRequest;
			chatPanel.hidden = false;
			chatTitle.textContent = openButton.dataset.messageChatName || 'Conversation';
			chatStatus.textContent = 'Loading messages...';
			chatList.replaceChildren();
			chatForm.dataset.sendUrl = openButton.dataset.messageChatSendUrl;
			chatInput.disabled = false;
			chatForm.querySelector('button[type="submit"]').disabled = false;

			try {
				const response = await fetch(openButton.dataset.messageChatUrl, {
					headers: { Accept: 'application/json' },
					credentials: 'same-origin',
				});

				const mirrorTranslationForm = document.querySelector('#mirror-translation-form');
				if (mirrorTranslationForm) {
					const sourceText = mirrorTranslationForm.querySelector('#mirror-source-text');
					const resultText = mirrorTranslationForm.querySelector('#mirror-result');
					const status = mirrorTranslationForm.querySelector('#mirror-translation-status');
					const count = mirrorTranslationForm.querySelector('#mirror-character-count');
					const submit = mirrorTranslationForm.querySelector('button[type="submit"]');

					sourceText.addEventListener('input', () => {
						count.textContent = sourceText.value.length;
					});

					mirrorTranslationForm.addEventListener('submit', async (event) => {
						event.preventDefault();
						submit.disabled = true;
						status.textContent = 'Translating...';
						resultText.value = '';

						try {
							const response = await fetch(mirrorTranslationForm.action, {
								method: 'POST',
								credentials: 'same-origin',
								headers: {
									Accept: 'application/json',
									'Content-Type': 'application/json',
									'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
									'X-Requested-With': 'XMLHttpRequest',
								},
								body: JSON.stringify({
									text: sourceText.value,
									source: mirrorTranslationForm.querySelector('[name="source"]').value,
									target: mirrorTranslationForm.querySelector('[name="target"]').value,
								}),
							});
							const data = await response.json();
							if (!response.ok) {
								throw new Error(data.message || mirrorTranslationForm.dataset.error);
							}
							resultText.value = data.translation;
							status.textContent = '';
						} catch (error) {
							status.textContent = error.message || mirrorTranslationForm.dataset.error;
						} finally {
							submit.disabled = false;
						}
					});
				}

				if (!response.ok) {
					throw new Error('Unable to load conversation');
				}

				const conversation = await response.json();
				openButton.classList.remove('dropdown-item--unread', 'fw-semibold', 'unread-row');
				const unreadBadge = document.querySelector('#unread-messages-badge');
				if (unreadBadge && Number.isInteger(conversation.read_count)) {
					const currentCount = Number.parseInt(unreadBadge.textContent, 10) || 0;
					const remainingCount = Math.max(0, currentCount - conversation.read_count);
					unreadBadge.textContent = remainingCount;
					unreadBadge.hidden = remainingCount < 1;
				} else if (unreadBadge && Number.isInteger(conversation.unread_count)) {
					unreadBadge.textContent = conversation.unread_count;
					unreadBadge.hidden = conversation.unread_count < 1;
				}

				if (requestId !== activeRequest) {
					return;
				}

				chatTitle.textContent = conversation.participant.name || chatTitle.textContent;
				chatStatus.textContent = conversation.messages.length ? '' : 'No messages in this conversation.';
				chatInput.disabled = !conversation.can_send;
				chatForm.querySelector('button[type="submit"]').disabled = !conversation.can_send;
				if (!conversation.can_send) {
					chatStatus.textContent = 'Messaging is unavailable because one of you blocked the other.';
				}

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

		const translateButton = event.target.closest('[data-message-action="translate"]');
		if (translateButton) {
			const item = translateButton.closest('.message-chat-item');
			const result = item?.querySelector('.message-chat-translation');
			if (!item || !result || translateButton.disabled) {
				return;
			}

			translateButton.disabled = true;
			chatStatus.textContent = chatPanel.dataset.translationLoading;
			try {
				const response = await fetch(translateButton.dataset.translateUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: requestHeaders,
				});
				const data = await response.json();
				if (!response.ok) {
					throw new Error(data.message || chatPanel.dataset.translationError);
				}
				result.textContent = data.translation;
				result.hidden = false;
				chatStatus.textContent = '';
			} catch (error) {
				chatStatus.textContent = error.message || chatPanel.dataset.translationError;
			} finally {
				translateButton.disabled = false;
			}
			return;
		}

		const actionButton = event.target.closest('[data-message-action="edit"], [data-message-action="delete"]');
		if (actionButton) {
			const item = actionButton.closest('.message-chat-item');
			if (!item) {
				return;
			}

			const message = actionButton.dataset.messageAction === 'edit'
				? 'edit'
				: 'delete';
			const messageId = item.dataset.messageId;
			const messageData = item.dataset;

			if (message === 'delete') {
				if (!window.confirm('Delete this message?')) {
					return;
				}

				actionButton.disabled = true;
				try {
					const response = await fetch(messageData.deleteUrl, {
						method: 'DELETE',
						credentials: 'same-origin',
						headers: requestHeaders,
					});
					const result = await response.json();
					if (!response.ok) {
						throw new Error(result.message || 'Could not delete message.');
					}
					item.remove();
				} catch (error) {
					actionButton.disabled = false;
					chatStatus.textContent = error.message || 'Could not delete message.';
				}
				return;
			}

			const body = item.querySelector('p');
			const editor = document.createElement('textarea');
			editor.className = 'form-control message-chat-edit';
			editor.value = body.textContent;
			body.replaceWith(editor);
			actionButton.setAttribute('aria-label', 'Save message');
			actionButton.title = 'Save message';
			actionButton.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i>';
			actionButton.dataset.messageAction = 'save';
			const cancelButton = item.querySelector('[data-message-action="delete"]');
			cancelButton.setAttribute('aria-label', 'Cancel editing');
			cancelButton.title = 'Cancel editing';
			cancelButton.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
			cancelButton.dataset.messageAction = 'cancel';
			editor.focus();
			return;
		}

		const saveButton = event.target.closest('[data-message-action="save"]');
		if (saveButton) {
			const item = saveButton.closest('.message-chat-item');
			const editor = item?.querySelector('.message-chat-edit');
			const messageText = editor?.value.trim();
			if (!item || !editor || !messageText) {
				return;
			}

			saveButton.disabled = true;
			try {
				const response = await fetch(item.dataset.updateUrl, {
					method: 'PATCH',
					credentials: 'same-origin',
					headers: requestHeaders,
					body: JSON.stringify({ message: messageText }),
				});
				const result = await response.json();
				if (!response.ok) {
					throw new Error(result.message || 'Could not update message.');
				}
				replaceMessage(item, result.message);
			} catch (error) {
				saveButton.disabled = false;
				chatStatus.textContent = error.message || 'Could not update message.';
			}
			return;
		}

		const cancelButton = event.target.closest('[data-message-action="cancel"]');
		if (cancelButton) {
			const item = cancelButton.closest('.message-chat-item');
			const editor = item?.querySelector('.message-chat-edit');
			if (!item || !editor) {
				return;
			}
			const body = document.createElement('p');
			body.textContent = editor.defaultValue;
			editor.replaceWith(body);
			cancelButton.setAttribute('aria-label', 'Delete message');
			cancelButton.title = 'Delete message';
			cancelButton.innerHTML = '<i class="fa-solid fa-trash" aria-hidden="true"></i>';
			cancelButton.dataset.messageAction = 'delete';
			const editButton = item.querySelector('[data-message-action="save"]');
			editButton.setAttribute('aria-label', 'Edit message');
			editButton.title = 'Edit message';
			editButton.innerHTML = '<i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>';
			editButton.dataset.messageAction = 'edit';
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
		if (!message || !chatForm.dataset.sendUrl || chatInput.disabled) {
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
			submitButton.disabled = chatInput.disabled;
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

document.addEventListener('click', async (event) => {
	const button = event.target.closest('.content-translate-button');
	if (!button || button.disabled) {
		return;
	}

	const container = button.closest('.content-translation-source')?.parentElement || button.parentElement;
	const result = container?.querySelector('.content-translation-result');
	if (!result || !button.dataset.translationUrl) {
		return;
	}

	button.disabled = true;
	result.hidden = false;
	result.textContent = button.dataset.translationLoading || 'Searching...';

	try {
		const response = await fetch(button.dataset.translationUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				Accept: 'application/json',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
				'X-Requested-With': 'XMLHttpRequest',
			},
		});
		const data = await response.json();
		if (!response.ok) {
			throw new Error(data.message || button.dataset.translationError || 'Translation is unavailable right now.');
		}
		result.textContent = data.translation;
	} catch (error) {
		result.textContent = error.message || button.dataset.translationError || 'Translation is unavailable right now.';
	} finally {
		button.disabled = false;
	}
});
