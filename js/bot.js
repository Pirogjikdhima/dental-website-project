const chatIcon = document.getElementById('chatIcon');
const chatContainer = document.getElementById('chatContainer');
const messageInput = document.getElementById('messageInput');
const sendButton = document.getElementById('sendButton');
const chatMessages = document.getElementById('chatMessages');
const chatClose = document.getElementById('chatClose');


function toggleChat(show) {
    if (show) {
        chatIcon.style.display = 'none';
        chatContainer.style.display = 'flex';
        setTimeout(() => {
            chatContainer.style.opacity = '1';
            chatContainer.style.transform = 'translateY(0)';
        }, 10);
    } else {
        chatContainer.style.opacity = '0';
        chatContainer.style.transform = 'translateY(20px)';
        setTimeout(() => {
            chatContainer.style.display = 'none';
            chatIcon.style.display = 'flex';
        }, 200);
    }
}

chatIcon.addEventListener('click', () => toggleChat(true));
chatClose.addEventListener('click', () => toggleChat(false));

document.addEventListener('click', (e) => {
    if (!chatContainer.contains(e.target) && !chatIcon.contains(e.target)) {
        toggleChat(false);
    }
});

function createMessage(text, isUser = true) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isUser ? 'user' : 'other'}`;

    const bubbleDiv = document.createElement('div');
    bubbleDiv.className = 'message-bubble';
    bubbleDiv.textContent = text;

    messageDiv.appendChild(bubbleDiv);
    return messageDiv;
}

function createLoadingIndicator() {
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'loading';
    loadingDiv.innerHTML = `
        <div class="loading-dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
    `;
    return loadingDiv;
}

async function sendMessage() {
    const text = messageInput.value.trim();
    if (text) {
        chatMessages.appendChild(createMessage(text));
        messageInput.value = '';
        sendButton.disabled = true;

        const loadingIndicator = createLoadingIndicator();
        chatMessages.appendChild(loadingIndicator);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        try {
            const response = await fetch('./bot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: JSON.stringify({message: text})
            });

            const data = await response.json();

            loadingIndicator.remove();

            if (data.success) {
                chatMessages.appendChild(createMessage(data.message, false));
            } else {
                chatMessages.appendChild(createMessage("I'm sorry, I'm having trouble understanding right now. Please try again later.", false));
            }
        } catch (error) {
            loadingIndicator.remove();
            chatMessages.appendChild(createMessage("Sorry, there was an error processing your message. Please try again.", false));
        }

        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

messageInput.addEventListener('input', () => {
    sendButton.disabled = !messageInput.value.trim();
});

messageInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !e.shiftKey && messageInput.value.trim()) {
        e.preventDefault();
        sendMessage();
    }
});

sendButton.addEventListener('click', sendMessage);


