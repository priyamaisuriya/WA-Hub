<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    /**
     * Verify the webhook URL with Meta.
     */
    public function verifyWebhook(Request $request)
    {
        $verifyToken = env('WHATSAPP_VERIFY_TOKEN');

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === $verifyToken) {
                return response($challenge, 200);
            }
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Handle incoming webhook events from Meta (messages, status updates).
     */
    public function handleWebhook(Request $request)
    {
        $data = $request->all();
        Log::info('Webhook received: ', $data);

        // Check if this is a WhatsApp message event
        if (isset($data['object']) && $data['object'] === 'whatsapp_business_account') {
            foreach ($data['entry'] as $entry) {
                foreach ($entry['changes'] as $change) {
                    $value = $change['value'];

                    // 1. Handle Incoming Messages
                    if (isset($value['messages'])) {
                        $messageData = $value['messages'][0];
                        $contactData = $value['contacts'][0];

                        $waId = $contactData['wa_id'];
                        $name = $contactData['profile']['name'] ?? 'Unknown';
                        $phone = $messageData['from'];
                        $body = $messageData['text']['body'] ?? '';
                        $msgId = $messageData['id'];

                        // Find or create the contact
                        $contact = Contact::firstOrCreate(
                            ['wa_id' => $waId],
                            ['name' => $name, 'phone_number' => $phone]
                        );

                        // Save the incoming message
                        Message::create([
                            'contact_id' => $contact->id,
                            'body' => $body,
                            'direction' => 'inbound',
                            'wa_message_id' => $msgId,
                            'status' => 'delivered'
                        ]);
                    }

                    // 2. Handle Message Status Updates (read, delivered, sent)
                    if (isset($value['statuses'])) {
                        $statusData = $value['statuses'][0];
                        $msgId = $statusData['id'];
                        $status = $statusData['status']; // sent, delivered, read

                        Message::where('wa_message_id', $msgId)->update(['status' => $status]);
                    }
                }
            }
        }

        // Return a 200 OK so Meta knows we received it
        return response('EVENT_RECEIVED', 200);
    }

    /**
     * Send a message to a WhatsApp user via the Graph API.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'text' => 'required|string'
        ]);

        $phone = $request->phone;
        $text = $request->text;
        
        $token = env('WHATSAPP_TOKEN');
        $phoneId = env('WHATSAPP_PHONE_ID');
        
        if (!$token || !$phoneId) {
            return response()->json(['error' => 'API keys not configured.'], 500);
        }

        $url = "https://graph.facebook.com/v19.0/{$phoneId}/messages";

        $response = Http::withToken($token)->post($url, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $text
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $waMessageId = $data['messages'][0]['id'] ?? null;

            // Save outgoing message to our DB
            $contact = Contact::where('phone_number', $phone)->orWhere('wa_id', $phone)->first();
            
            if ($contact) {
                Message::create([
                    'contact_id' => $contact->id,
                    'body' => $text,
                    'direction' => 'outbound',
                    'wa_message_id' => $waMessageId,
                    'status' => 'sent'
                ]);
            }

            return response()->json(['success' => true, 'data' => $data]);
        }

        Log::error('Meta API Send Message Failed: ', $response->json());
        return response()->json(['error' => 'Failed to send message', 'details' => $response->json()], 400);
    }

    /**
     * Show the Chat UI page
     */
    public function chatIndex()
    {
        return view('chat');
    }

    /**
     * API to get all contacts
     */
    public function getContacts()
    {
        $contacts = Contact::orderBy('updated_at', 'desc')->get();
        return response()->json($contacts);
    }

    /**
     * API to get messages for a specific contact
     */
    public function getMessages($contactId)
    {
        $messages = Message::where('contact_id', $contactId)
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json($messages);
    }

    /**
     * API to add a new contact manually
     */
    public function addContact(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'name' => 'required|string'
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->phone);

        $contact = Contact::firstOrCreate(
            ['phone_number' => $phone],
            ['name' => $request->name, 'wa_id' => $phone]
        );

        return response()->json($contact);
    }
}
