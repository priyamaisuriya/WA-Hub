# WhatsApp Cloud API Web Chat Integration

This is a Laravel-based web application that integrates directly with the **WhatsApp Cloud API**. It provides a fully functional, real-time web interface where you can view incoming WhatsApp messages from your customers and reply to them directly from your computer.

## Features
- **Real-Time Webhooks:** Instantly receives incoming messages from the WhatsApp Cloud API.
- **Outbound Messaging:** Send replies directly to customers from the web UI.
- **Contact Management:** Automatically saves new contacts when they message you.
- **Chat Interface:** A modern, dark-themed responsive chat UI designed for desktop and mobile.

---

## 🚀 Getting Started

### Prerequisites
1. PHP 8.1+ and Composer
2. A Meta Developer Account
3. A WhatsApp Business Account (WABA) with a registered Phone Number
4. **Ngrok** (for local development webhooks)

### 1. Installation

Clone the repository and install dependencies:
```bash
git clone <your-repo-url>
cd chat-app
composer install
```

Copy the environment file and generate the app key:
```bash
cp .env.example .env
php artisan key:generate
```

### 2. Database Setup

This project uses SQLite by default. Ensure the database file exists:
```bash
touch database/database.sqlite
```
Run the migrations to create the Contacts and Messages tables:
```bash
php artisan migrate
```

### 3. Meta Developer & Environment Setup

In your `.env` file, you need to add your WhatsApp Cloud API credentials.

```env
WHATSAPP_TOKEN=your_permanent_or_temporary_access_token
WHATSAPP_PHONE_ID=your_phone_number_id
WHATSAPP_BUSINESS_ACCOUNT_ID=your_waba_id
WHATSAPP_VERIFY_TOKEN=wa_hub_secure_token
```

### 4. Running Locally & Setting up Webhooks

To receive messages locally, you must expose your local server to the internet using **Ngrok**.

1. Start your Laravel development server:
```bash
php artisan serve
```

2. In a new terminal, start Ngrok on port 8000:
```bash
ngrok http 8000
```

3. Go to your **Meta Developer Dashboard -> WhatsApp -> API Setup**.
4. Set your Webhook URL to: `https://<your-ngrok-url>.ngrok-free.dev/webhook`
5. Set your Verify Token to: `wa_hub_secure_token`
6. Click **Verify and Save**.
7. Click **Manage** and subscribe to the `messages` event.

### 5. Start Chatting!

Open your browser and navigate to:
`http://localhost:8000`

Send a message from your personal phone to your WhatsApp Business number, and it will instantly pop up in your web chat UI!

---

## Technical Stack
- **Backend:** Laravel 11 (PHP)
- **Database:** SQLite
- **Frontend UI:** Blade Templates, Vanilla CSS, Flexbox
- **API:** Meta Graph API v19.0
