<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp Chat Integration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .chat-bg {
            background-color: #f0fdf4;
            background-image: radial-gradient(#dcfce7 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .scrollbar-w-2::-webkit-scrollbar { width: 6px; }
        .scrollbar-w-2::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-w-2::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        .message-bubble {
            animation: slideIn 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-100 via-purple-50 to-teal-50 h-screen flex justify-center items-center p-4 sm:p-8">
    <!-- Main App Container -->
    <div class="w-full max-w-[1200px] h-full sm:h-[90vh] glass-panel shadow-[0_20px_50px_rgba(0,0,0,0.1)] rounded-3xl flex overflow-hidden relative" id="app">
        
        <!-- Sidebar (Contacts) -->
        <div class="w-full sm:w-1/3 flex flex-col border-r border-gray-200/60 bg-white/40">
            <!-- Header -->
            <div class="h-20 flex items-center px-6 border-b border-gray-200/60 bg-white/50 backdrop-blur-md z-10">
                <div class="relative">
                    <img src="https://ui-avatars.com/api/?name=Business&background=4F46E5&color=fff" alt="User" class="w-12 h-12 rounded-full ring-2 ring-white shadow-sm">
                    <div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></div>
                </div>
                <div class="ml-4">
                    <h1 class="font-bold text-gray-800 text-lg tracking-tight">My WA Business</h1>
                    <p class="text-xs text-indigo-600 font-medium">Online</p>
                </div>
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
        <div class="w-full sm:w-2/3 flex flex-col bg-slate-50/50 relative">
            <!-- Default screen -->
            <div id="default-screen" class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-white z-10">
                <div class="text-center transform transition-all hover:scale-105 duration-500">
                    <div class="w-24 h-24 bg-indigo-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-indigo-100/50 rotate-3 hover:rotate-0 transition-transform">
                        <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 tracking-tight">WhatsApp Hub</h2>
                    <p class="text-gray-500 mt-3 font-medium">Select a conversation to start messaging</p>
                </div>
            </div>

            <!-- Chat Header -->
            <div class="h-20 bg-white/70 backdrop-blur-md flex items-center px-6 border-b border-gray-200/60 shadow-sm z-20">
                <img id="chat-header-img" src="" alt="Contact" class="w-12 h-12 rounded-full hidden ring-2 ring-white shadow-sm">
                <div class="ml-4">
                    <h2 id="chat-header-name" class="font-bold text-gray-800 text-lg"></h2>
                    <p id="chat-header-phone" class="text-sm text-gray-500 font-medium"></p>
                </div>
                <div class="ml-auto flex gap-3">
                    <button class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-full transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></button>
                    <button class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-full transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg></button>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-6 chat-bg scrollbar-w-2" id="messages-container">
            </div>

            <!-- Message Input Area -->
            <div class="p-4 bg-white/60 backdrop-blur-md border-t border-gray-200/60 z-20">
                <div class="flex items-center bg-white rounded-full p-1.5 shadow-sm border border-gray-200 focus-within:ring-2 focus-within:ring-indigo-100 focus-within:border-indigo-400 transition-all">
                    <button class="p-2 text-gray-400 hover:text-indigo-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                    <input type="text" id="message-input" placeholder="Type a message..." class="flex-1 bg-transparent px-4 py-2 focus:outline-none text-gray-700 placeholder-gray-400" disabled onkeypress="handleKeyPress(event)">
                    <button id="send-button" onclick="sendMessage()" class="ml-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-full p-2.5 hover:shadow-lg hover:shadow-indigo-500/30 transform hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:transform-none" disabled>
                        <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logic -->
    <script>
        let currentContactId = null;
        let currentPhone = null;
        let refreshInterval = null;

        async function fetchContacts() {
            try {
                const response = await fetch('/api/contacts');
                const contacts = await response.json();
                const container = document.getElementById('contacts-list');
                
                if (contacts.length === 0) {
                    container.innerHTML = '<div class="p-8 text-center text-gray-500 text-sm bg-indigo-50/50 m-4 rounded-2xl border border-indigo-100">No contacts yet.</div>';
                    return;
                }

                let html = '';
                contacts.forEach(contact => {
                    const isActive = currentContactId === contact.id;
                    const activeClasses = isActive ? 'bg-indigo-50 border-indigo-100 shadow-sm' : 'hover:bg-slate-50 border-transparent hover:shadow-sm';
                    const name = contact.name || contact.phone_number;

                    html += `
                    <div class="flex items-center px-4 py-4 mx-3 my-1 cursor-pointer rounded-2xl border transition-all duration-200 transform hover:-translate-y-0.5 ${activeClasses}" onclick="selectContact(${contact.id}, '${name.replace(/'/g, "\\'")}', '${contact.phone_number}')">
                        <div class="relative">
                            <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random" class="w-12 h-12 rounded-full shadow-sm">
                            ${isActive ? '<div class="absolute -top-1 -right-1 w-3 h-3 bg-indigo-500 rounded-full border-2 border-white"></div>' : ''}
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="font-bold text-gray-800 ${isActive ? 'text-indigo-700' : ''}">${name}</h3>
                            <p class="text-xs text-gray-500 truncate font-medium mt-0.5">${contact.phone_number}</p>
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
                    const bgClass = isOutbound ? 'bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-white text-gray-800 shadow-sm border border-gray-100';
                    const radiusClass = isOutbound ? 'rounded-2xl rounded-tr-sm' : 'rounded-2xl rounded-tl-sm';
                    
                    let statusTick = '';
                    if (isOutbound) {
                        const color = msg.status === 'read' ? 'text-blue-300' : 'text-indigo-300';
                        statusTick = `<span class="ml-1.5 text-[11px] font-bold ${color}">✓✓</span>`;
                    }

                    html += `
                    <div class="flex ${alignClass} mb-4 message-bubble">
                        <div class="max-w-[75%] ${bgClass} px-4 py-2.5 ${radiusClass} relative">
                            <p class="text-[15px] leading-relaxed break-words">${msg.body}</p>
                            <div class="text-[10px] ${isOutbound ? 'text-indigo-100' : 'text-gray-400'} text-right mt-1.5 flex justify-end items-center font-medium tracking-wide">
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

        fetchContacts();
        setInterval(fetchContacts, 5000);
    </script>
</body>
</html>
