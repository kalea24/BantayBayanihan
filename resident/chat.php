<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'resident') {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantay AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #chatContainer {
            height: 600px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        .chat-message {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 8px;
            max-width: 80%;
        }
        .user-message {
            background: #007bff;
            color: white;
            margin-left: auto;
        }
        .ai-message {
            background: white;
            color: black;
            border: 1px solid #ddd;
        }
        .ai-avatar {
            width: 40px;
            height: 40px;
            background: #dc3545;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }
        .quick-questions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        .quick-btn {
            border-radius: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">🚨 Bantay Bayanihan</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="evacuate.php">Evacuate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="drill.php">Drill Mode</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="chat.php">Bantay AI</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">👤 <?= htmlspecialchars($_SESSION['first_name']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../api/auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4>🤖 Bantay AI - Your Disaster Preparedness Assistant</h4>
                        <p class="mb-0 small">Ask me anything about evacuation, emergency preparedness, or disaster response!</p>
                    </div>
                    <div class="card-body">
                        <!-- Chat Container -->
                        <div id="chatContainer">
                            <div class="chat-message ai-message d-flex align-items-start">
                                <div class="ai-avatar">🤖</div>
                                <div>
                                    <strong>Bantay AI</strong>
                                    <p class="mb-0">Kumusta! I'm Bantay, your disaster preparedness assistant. I can help you with:</p>
                                    <ul class="mt-2 mb-0">
                                        <li>Finding evacuation centers</li>
                                        <li>Emergency procedures for earthquakes, floods, fires, and typhoons</li>
                                        <li>Creating emergency kits</li>
                                        <li>Safety tips and preparedness advice</li>
                                    </ul>
                                    <p class="mt-2 mb-0">How can I assist you today?</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Question Buttons -->
                        <div class="quick-questions">
                            <button class="btn btn-outline-primary btn-sm quick-btn" onclick="askQuestion('Where is the nearest evacuation center?')">
                                📍 Nearest evacuation center
                            </button>
                            <button class="btn btn-outline-primary btn-sm quick-btn" onclick="askQuestion('What should I do during an earthquake?')">
                                🌍 Earthquake safety
                            </button>
                            <button class="btn btn-outline-primary btn-sm quick-btn" onclick="askQuestion('How do I prepare an emergency kit?')">
                                🎒 Emergency kit
                            </button>
                            <button class="btn btn-outline-primary btn-sm quick-btn" onclick="askQuestion('What are the flood safety tips?')">
                                🌊 Flood safety
                            </button>
                        </div>

                        <!-- Input Form -->
                        <form id="chatForm" class="mt-3">
                            <div class="input-group">
                                <input type="text" id="messageInput" class="form-control" 
                                       placeholder="Type your question here..." required>
                                <button class="btn btn-danger" type="submit">Send</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const chatContainer = document.getElementById('chatContainer');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');

        // Send message
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            // Add user message
            addMessage(message, 'user');
            messageInput.value = '';

            // Show typing indicator
            const typingId = addTypingIndicator();

            // Send to API
            try {
                const response = await fetch('../api/chatbot/chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message })
                });

                const data = await response.json();
                
                // Remove typing indicator
                document.getElementById(typingId).remove();

                // Add AI response
                if (data.success) {
                    addMessage(data.response, 'ai');
                } else {
                    addMessage('Sorry, I encountered an error. Please try again.', 'ai');
                }
            } catch (error) {
                document.getElementById(typingId).remove();
                addMessage('Sorry, I encountered an error. Please try again.', 'ai');
            }
        });

        // Quick question
        function askQuestion(question) {
            messageInput.value = question;
            chatForm.dispatchEvent(new Event('submit'));
        }

        // Add message to chat
        function addMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${type}-message d-flex align-items-start`;

            if (type === 'ai') {
                messageDiv.innerHTML = `
                    <div class="ai-avatar">🤖</div>
                    <div>
                        <strong>Bantay AI</strong>
                        <p class="mb-0">${text}</p>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `<p class="mb-0">${text}</p>`;
            }

            chatContainer.appendChild(messageDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // Add typing indicator
        function addTypingIndicator() {
            const id = 'typing-' + Date.now();
            const typingDiv = document.createElement('div');
            typingDiv.id = id;
            typingDiv.className = 'chat-message ai-message d-flex align-items-start';
            typingDiv.innerHTML = `
                <div class="ai-avatar">🤖</div>
                <div>
                    <strong>Bantay AI</strong>
                    <p class="mb-0">Typing...</p>
                </div>
            `;
            chatContainer.appendChild(typingDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
            return id;
        }
    </script>
</body>
</html>