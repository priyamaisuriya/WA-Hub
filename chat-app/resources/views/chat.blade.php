<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp Chat Integration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
        .chat-bg {
            background-color: #0f172a;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23334155' fill-opacity='0.2'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .scrollbar-w-2::-webkit-scrollbar { width: 6px; }
        .scrollbar-w-2::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-w-2::-webkit-scrollbar-thumb { background-color: #334155; border-radius: 20px; }
        .scrollbar-w-2:hover::-webkit-scrollbar-thumb { background-color: #475569; }
        
        .glass-panel {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        
        .message-bubble {
            animation: slideIn 0.3s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }
        .bubble-outbound { border-radius: 18px 18px 4px 18px; transform-origin: bottom right; }
        .bubble-inbound { border-radius: 18px 18px 18px 4px; transform-origin: bottom left; }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(15px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>
</head>
<body class="bg-slate-900 h-screen flex justify-center items-center p-4 sm:p-8 relative overflow-hidden">
    <!-- Ambient glowing orbs for background -->
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-600/20 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-purple-600/20 blur-[120px] pointer-events-none"></div>
    <!-- Main App Container -->
    <div class="w-full max-w-[1200px] h-full sm:h-[90vh] glass-panel shadow-[0_20px_50px_rgba(0,0,0,0.1)] rounded-3xl flex overflow-hidden relative" id="app">
        
        <!-- Sidebar (Contacts) -->
        <div class="w-full sm:w-1/3 flex flex-col border-r border-slate-700/50 bg-slate-900/40 z-10">
            <!-- Header -->
            <div class="h-20 flex items-center px-6 border-b border-slate-700/50 bg-slate-900/60 backdrop-blur-md">
                <div class="relative">
                    <img src="https://ui-avatars.com/api/?name=Business&background=6366f1&color=fff" alt="User" class="w-12 h-12 rounded-full ring-2 ring-slate-800 shadow-sm">
                    <div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-400 border-2 border-slate-900 rounded-full"></div>
                </div>
                <div class="ml-4 flex-1">
                    <h1 class="font-bold text-slate-100 text-lg tracking-tight">My WA Business</h1>
                    <p class="text-xs text-indigo-400 font-medium">Online</p>
                </div>
                <button onclick="promptAddContact()" class="ml-auto bg-slate-800 hover:bg-slate-700 text-indigo-400 hover:text-indigo-300 p-2.5 rounded-full transition-all hover:scale-105 active:scale-95" title="New Chat">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
            </div>

            <!-- Contacts List -->
            <div class="flex-1 overflow-y-auto scrollbar-w-2 pt-2" id="contacts-list">
                <div class="p-8 text-center text-gray-500 text-sm">
                    <div class="animate-pulse flex flex-col items-center">
                        <div class="w-12 h-12 bg-gray-200 rounded-full mb-3"></div>
                        <div class="h-4 bg-gray-200 rounded w-24"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="w-full sm:w-2/3 flex flex-col bg-slate-900/50 relative z-10">
            <!-- Default screen -->
            <div id="default-screen" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 z-20 transition-opacity duration-300">
                <div class="text-center transform transition-all hover:scale-105 duration-500">
                    <div class="w-24 h-24 bg-slate-800 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-indigo-500/10 rotate-3 hover:rotate-0 transition-transform border border-slate-700">
                        <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <h2 class="text-3xl font-bold text-slate-100 tracking-tight">WhatsApp Hub</h2>
                    <p class="text-slate-400 mt-3 font-medium">Select a conversation to start messaging</p>
                </div>
            </div>

            <!-- Chat Header -->
            <div class="h-20 bg-slate-900/70 backdrop-blur-md flex items-center px-6 border-b border-slate-700/50 shadow-sm z-10">
                <img id="chat-header-img" src="" alt="Contact" class="w-12 h-12 rounded-full hidden ring-2 ring-slate-800 shadow-sm">
                <div class="ml-4">
                    <h2 id="chat-header-name" class="font-bold text-slate-100 text-lg"></h2>
                    <p id="chat-header-phone" class="text-sm text-slate-400 font-medium"></p>
                </div>
                <div class="ml-auto flex gap-3">
                    <button class="p-2 text-slate-400 hover:text-indigo-400 hover:bg-slate-800 rounded-full transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></button>
                    <button class="p-2 text-slate-400 hover:text-indigo-400 hover:bg-slate-800 rounded-full transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg></button>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-6 chat-bg scrollbar-w-2" id="messages-container">
            </div>

            <!-- Message Input Area -->
            <div class="p-4 bg-slate-900/70 backdrop-blur-md border-t border-slate-700/50 z-10">
                <div class="flex items-center bg-slate-800/80 rounded-full p-1.5 shadow-inner border border-slate-700 focus-within:ring-2 focus-within:ring-indigo-500/50 focus-within:border-indigo-500 transition-all relative">
                    <button id="emoji-button" class="p-2.5 text-slate-400 hover:text-indigo-400 transition-colors relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                    <div id="emoji-picker-container" class="absolute bottom-16 left-0 hidden z-50 shadow-2xl rounded-xl overflow-hidden border border-slate-700/50 transform origin-bottom-left transition-all">
                        <emoji-picker class="dark" style="--background: #1e293b; --border-color: #334155; --num-columns: 8;"></emoji-picker>
                    </div>
                    <input type="text" id="message-input" placeholder="Type a message..." class="flex-1 bg-transparent px-4 py-2 focus:outline-none text-slate-200 placeholder-slate-500" disabled onkeypress="handleKeyPress(event)">
                    <button id="send-button" onclick="sendMessage()" class="ml-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-full p-3 shadow-lg shadow-indigo-500/20 transform hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:transform-none disabled:shadow-none" disabled>
                        <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Contact Modal -->
    <div id="addContactModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex justify-center items-center">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl transform transition-all">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">New Chat</h3>
                <button onclick="closeAddContactModal()" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number (with country code)</label>
                    <input type="text" id="newContactPhone" placeholder="e.g. 919979941652" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
                    <input type="text" id="newContactName" placeholder="e.g. John Doe" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-gray-50">
                </div>
                <button onclick="submitNewContact()" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:shadow-lg hover:shadow-indigo-500/30 text-white font-medium py-3 rounded-xl transition-all transform hover:-translate-y-0.5 mt-4">
                    Start Chat
                </button>
            </div>
        </div>
    </div>

    <!-- Logic -->
    <script>
        let currentContactId = null;
        let currentPhone = null;
        let refreshInterval = null;

        function promptAddContact() {
            document.getElementById('addContactModal').classList.remove('hidden');
            document.getElementById('newContactPhone').focus();
        }

        function closeAddContactModal() {
            document.getElementById('addContactModal').classList.add('hidden');
            document.getElementById('newContactPhone').value = '';
            document.getElementById('newContactName').value = '';
        }

        async function submitNewContact() {
            const phone = document.getElementById('newContactPhone').value.trim();
            if (!phone) return;
            
            const name = document.getElementById('newContactName').value.trim() || phone;
            
            try {
                const response = await fetch('/api/contacts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ phone, name })
                });
                
                if (response.ok) {
                    const contact = await response.json();
                    await fetchContacts();
                    closeAddContactModal();
                    setTimeout(() => selectContact(contact.id, contact.name, contact.phone_number), 500);
                } else {
                    alert("Failed to add contact. Please check the phone number.");
                }
            } catch (err) {
                console.error("Error adding contact", err);
            }
        }

        async function fetchContacts() {
            try {
                const response = await fetch('/api/contacts');
                const contacts = await response.json();
                const container = document.getElementById('contacts-list');
                
                if (contacts.length === 0) {
                    container.innerHTML = '<div class="p-8 text-center text-slate-400 text-sm bg-slate-800/50 m-4 rounded-2xl border border-slate-700/50">No contacts yet.</div>';
                    return;
                }

                let html = '';
                contacts.forEach(contact => {
                    const isActive = currentContactId === contact.id;
                    const activeClasses = isActive ? 'bg-slate-800 border-slate-700 shadow-md' : 'hover:bg-slate-800/50 border-transparent hover:shadow-sm';
                    const name = contact.name || contact.phone_number;

                    html += `
                    <div class="flex items-center px-4 py-3 mx-3 my-1 cursor-pointer rounded-2xl border transition-all duration-200 transform hover:-translate-y-0.5 ${activeClasses}" onclick="selectContact(${contact.id}, '${name.replace(/'/g, "\\'")}', '${contact.phone_number}')">
                        <div class="relative">
                            <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random" class="w-12 h-12 rounded-full shadow-sm">
                            ${isActive ? '<div class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-400 rounded-full border-2 border-slate-800 shadow-[0_0_8px_rgba(52,211,153,0.6)]"></div>' : ''}
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="font-bold text-slate-200 ${isActive ? 'text-indigo-400' : ''}">${name}</h3>
                            <p class="text-xs text-slate-500 truncate font-medium mt-0.5">${contact.phone_number}</p>
                        </div>
                    </div>`;
                });

                if (window.lastContactsHtml !== html) {
                    window.lastContactsHtml = html;
                    container.innerHTML = html;
                }

                if (!currentContactId && contacts.length > 0) {
                    selectContact(contacts[0].id, contacts[0].name, contacts[0].phone_number);
                }
            } catch (err) {
                console.error("Failed to fetch contacts", err);
            }
        }

        function selectContact(id, name, phone) {
            currentContactId = id;
            currentPhone = phone;
            
            document.getElementById('default-screen').style.opacity = '0';
            setTimeout(() => document.getElementById('default-screen').classList.add('hidden'), 300);
            
            document.getElementById('chat-header-img').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name || phone)}&background=random`;
            document.getElementById('chat-header-img').classList.remove('hidden');
            document.getElementById('chat-header-name').innerText = name || phone;
            document.getElementById('chat-header-phone').innerText = phone;
            
            document.getElementById('message-input').disabled = false;
            document.getElementById('send-button').disabled = false;
            
            fetchMessages();
            fetchContacts(); 

            if (refreshInterval) clearInterval(refreshInterval);
            refreshInterval = setInterval(fetchMessages, 3000);
        }

        async function fetchMessages() {
            if (!currentContactId) return;
            
            try {
                const response = await fetch(`/api/messages/${currentContactId}`);
                const messages = await response.json();
                const container = document.getElementById('messages-container');
                
                const isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 50;

                let html = '';
                messages.forEach(msg => {
                    const isOutbound = msg.direction === 'outbound';
                    const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    
                    const alignClass = isOutbound ? 'justify-end' : 'justify-start';
                    const bgClass = isOutbound ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'bg-slate-800 text-slate-100 shadow-md border border-slate-700/50';
                    const radiusClass = isOutbound ? 'bubble-outbound' : 'bubble-inbound';
                    const paddingClass = isOutbound ? 'pr-4 pl-5 py-3' : 'pl-4 pr-5 py-3';
                    
                    let statusTick = '';
                    if (isOutbound) {
                        const color = msg.status === 'read' ? 'text-blue-400' : 'text-indigo-300';
                        statusTick = `<span class="ml-1.5 text-[11px] font-bold ${color}">✓✓</span>`;
                    }

                    html += `
                    <div class="flex ${alignClass} mb-5 message-bubble ${isOutbound ? 'outbound' : 'inbound'}">
                        <div class="max-w-[75%] ${bgClass} ${paddingClass} ${radiusClass} relative">
                            <p class="text-[15px] leading-relaxed break-words">${msg.body}</p>
                            <div class="text-[10px] ${isOutbound ? 'text-indigo-200' : 'text-slate-400'} text-right mt-1.5 flex justify-end items-center font-medium tracking-wide">
                                ${time} ${statusTick}
                            </div>
                        </div>
                    </div>`;
                });

                if (window.lastMessagesHtml !== html) {
                    window.lastMessagesHtml = html;
                    container.innerHTML = html;
                    if (isScrolledToBottom) {
                        container.scrollTop = container.scrollHeight;
                    }
                }

                if (isScrolledToBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            } catch (err) {
                console.error("Failed to fetch messages", err);
            }
        }

        async function sendMessage() {
            const input = document.getElementById('message-input');
            const text = input.value.trim();
            
            if (!text || !currentPhone) return;
            
            input.value = '';
            
            try {
                const response = await fetch('/api/send-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        phone: currentPhone,
                        text: text
                    })
                });
                
                if (response.ok) {
                    setTimeout(fetchMessages, 300);
                } else {
                    alert("Failed to send message. Please check API keys.");
                }
            } catch (err) {
                console.error("Error sending message", err);
            }
        }
        
        function handleKeyPress(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        }

        // Emoji Picker Logic
        const emojiButton = document.getElementById('emoji-button');
        const emojiContainer = document.getElementById('emoji-picker-container');
        const emojiPicker = document.querySelector('emoji-picker');
        const messageInput = document.getElementById('message-input');

        emojiButton.addEventListener('click', (e) => {
            emojiContainer.classList.toggle('hidden');
            e.stopPropagation();
        });

        document.addEventListener('click', (e) => {
            if (!emojiContainer.contains(e.target) && !emojiButton.contains(e.target)) {
                emojiContainer.classList.add('hidden');
            }
        });

        emojiPicker.addEventListener('emoji-click', event => {
            const emoji = event.detail.unicode;
            const startPos = messageInput.selectionStart || messageInput.value.length;
            const endPos = messageInput.selectionEnd || messageInput.value.length;
            messageInput.value = messageInput.value.substring(0, startPos) + emoji + messageInput.value.substring(endPos);
            messageInput.focus();
            messageInput.selectionStart = messageInput.selectionEnd = startPos + emoji.length;
        });

        fetchContacts();
        setInterval(fetchContacts, 5000);
    </script>
</body>
</html>
