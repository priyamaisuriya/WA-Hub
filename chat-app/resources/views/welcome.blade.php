<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat App UI</title>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="app-container">
        
        <!-- SIDEBAR -->
        <div class="sidebar">
            <!-- Sidebar Header -->
            <div class="sidebar-header">
                <div class="avatar">
                    <img src="https://ui-avatars.com/api/?name=User&background=6366f1&color=fff" alt="User">
                </div>
                <div class="header-icons">
                    <i class="fa-solid fa-bell"></i>
                    <i class="fa-solid fa-pen-to-square"></i>
                    <i class="fa-solid fa-gear"></i>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Search conversations...">
                </div>
            </div>

            <!-- Chat List -->
            <div class="chat-list">
                
                <div class="chat-item active">
                    <div class="avatar chat-item-avatar">
                        <div class="status-indicator online"></div>
                        <img src="https://ui-avatars.com/api/?name=AI&background=3b82f6&color=fff" alt="AI">
                    </div>
                    <div class="chat-item-info">
                        <div class="chat-top">
                            <span class="chat-name">AI Assistant</span>
                            <span class="chat-time">10:25 AM</span>
                        </div>
                        <div class="chat-msg">I've updated the theme for you!</div>
                    </div>
                </div>

                <div class="chat-item">
                    <div class="avatar chat-item-avatar">
                        <img src="https://ui-avatars.com/api/?name=John+Doe&background=f43f5e&color=fff" alt="John">
                    </div>
                    <div class="chat-item-info">
                        <div class="chat-top">
                            <span class="chat-name">John Doe</span>
                            <span class="chat-time">Yesterday</span>
                        </div>
                        <div class="chat-msg">Let's catch up later today.</div>
                    </div>
                </div>

                <div class="chat-item">
                    <div class="avatar chat-item-avatar">
                        <img src="https://ui-avatars.com/api/?name=Design+Team&background=10b981&color=fff" alt="Team">
                    </div>
                    <div class="chat-item-info">
                        <div class="chat-top">
                            <span class="chat-name">Design Team</span>
                            <span class="chat-time">Tuesday</span>
                        </div>
                        <div class="chat-msg">Alice: The new mockups look amazing!</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN CHAT AREA -->
        <div class="main-chat">
            
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-header-info">
                    <div class="avatar">
                        <div class="status-indicator online"></div>
                        <img src="https://ui-avatars.com/api/?name=AI&background=3b82f6&color=fff" alt="AI">
                    </div>
                    <div class="chat-header-text">
                        <div class="chat-header-name">AI Assistant</div>
                        <div class="chat-header-status">Active now</div>
                    </div>
                </div>
                <div class="header-icons">
                    <i class="fa-solid fa-phone"></i>
                    <i class="fa-solid fa-video"></i>
                    <i class="fa-solid fa-circle-info"></i>
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="chat-messages" id="chatMessages">
                
                <div class="message received">
                    <div class="bubble">
                        Hello! I'm your AI assistant. How can I help you today?
                        <div class="message-meta">
                            <span class="timestamp">10:24 AM</span>
                        </div>
                    </div>
                </div>

                <div class="message sent">
                    <div class="bubble">
                        Make a two-column chat layout, but give it a totally unique, modern light theme instead of copying WhatsApp.
                        <div class="message-meta">
                            <span class="timestamp">10:25 AM</span>
                            <span class="ticks"><i class="fa-solid fa-check-double"></i></span>
                        </div>
                    </div>
                </div>

                <div class="message received">
                    <div class="bubble">
                        Understood! I've removed the WhatsApp branding and created a unique, sleek modern interface. How does this look?
                        <div class="message-meta">
                            <span class="timestamp">10:25 AM</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Chat Input -->
            <div class="chat-input-area">
                <div class="input-actions">
                    <button class="icon-btn"><i class="fa-solid fa-plus"></i></button>
                    <button class="icon-btn"><i class="fa-regular fa-face-smile"></i></button>
                </div>
                <div class="chat-input-wrapper">
                    <input type="text" placeholder="Write a message..." id="messageInput" autocomplete="off">
                </div>
                <button class="send-btn" id="sendBtn">
                    <i class="fa-solid fa-paper-plane" id="sendIcon"></i>
                </button>
            </div>
        </div>

    </div>

    <script>
        const sendBtn = document.getElementById('sendBtn');
        const messageInput = document.getElementById('messageInput');
        const chatMessages = document.getElementById('chatMessages');

        async function sendMessage() {
            const text = messageInput.value.trim();
            if (!text) return;

            // Optional: hardcode the destination phone number for testing
            const phone = "1234567890"; // Replace with real test phone number

            // 1. Optimistically append message to UI
            const msgDiv = document.createElement('div');
            msgDiv.className = 'message sent';
            const now = new Date();
            const timeStr = now.getHours() + ':' + String(now.getMinutes()).padStart(2, '0');

            const bubbleDiv = document.createElement('div');
            bubbleDiv.className = 'bubble';
            bubbleDiv.innerHTML = text + `
                <div class="message-meta">
                    <span class="timestamp">${timeStr}</span>
                    <span class="ticks"><i class="fa-solid fa-clock"></i></span>
                </div>
            `;
            msgDiv.appendChild(bubbleDiv);
            chatMessages.appendChild(msgDiv);
            
            messageInput.value = '';
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // 2. Send to Backend API
            try {
                const response = await fetch('/api/send-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // For web.php routes
                    },
                    body: JSON.stringify({ phone: phone, text: text })
                });
                
                const data = await response.json();
                
                // Update tick to single tick (sent to server)
                const tick = msgDiv.querySelector('.ticks i');
                if(response.ok) {
                    if(tick) tick.className = "fa-solid fa-check";
                } else {
                    if(tick) tick.className = "fa-solid fa-circle-exclamation text-red-500";
                    console.error("Failed:", data);
                }
            } catch(e) {
                console.error("Error sending message", e);
                const tick = msgDiv.querySelector('.ticks i');
                if(tick) tick.className = "fa-solid fa-circle-exclamation text-red-500";
            }
        }

        sendBtn.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendMessage();
        });
    </script>
</body>
</html>
