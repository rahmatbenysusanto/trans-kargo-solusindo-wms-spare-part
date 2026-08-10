<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\AiChatService;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    private AiChatService $aiChatService;

    public function __construct(AiChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    /**
     * Send a message and get AI reply.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message'              => 'required|string|max:500',
            'chat_conversation_id' => 'nullable|exists:chat_conversations,id',
        ]);

        $user = auth()->user();

        // Find or create conversation
        if ($request->chat_conversation_id) {
            $conversation = ChatConversation::where('id', $request->chat_conversation_id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $conversation = ChatConversation::create([
                'user_id' => $user->id,
            ]);
        }

        $reply = $this->aiChatService->chat($request->message, $conversation);

        return response()->json([
            'success'              => true,
            'reply'                => $reply,
            'chat_conversation_id' => $conversation->id,
            'title'                => $conversation->title,
        ]);
    }

    /**
     * Get list of user's conversations.
     */
    public function conversations()
    {
        $conversations = ChatConversation::where('user_id', auth()->id())
            ->with(['messages' => function ($q) {
                $q->latest('created_at')->limit(1);
            }])
            ->latest('updated_at')
            ->limit(20)
            ->get()
            ->map(fn($c) => [
                'id'           => $c->id,
                'title'        => $c->title ?? 'Percakapan Baru',
                'updated_at'   => $c->updated_at->diffForHumans(),
                'last_message' => $c->messages->first()?->content
                    ? mb_substr($c->messages->first()->content, 0, 60) . '...'
                    : null,
            ]);

        return response()->json([
            'success'       => true,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Get messages for a specific conversation.
     */
    public function messages($conversationId)
    {
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $messages = $conversation->messages()->get()->map(fn($m) => [
            'id'         => $m->id,
            'role'       => $m->role,
            'content'    => $m->content,
            'created_at' => $m->created_at->format('H:i'),
        ]);

        return response()->json([
            'success'  => true,
            'title'    => $conversation->title,
            'messages' => $messages,
        ]);
    }

    /**
     * Delete a conversation.
     */
    public function deleteConversation($conversationId)
    {
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Percakapan berhasil dihapus.',
        ]);
    }
}
