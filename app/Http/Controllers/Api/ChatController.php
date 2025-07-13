<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use App\Models\MediaAsset;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends ApiController
{


    private function getConversation($user_id, $recipient_id, $property_id = null){
        return Conversation::firstOrCreate([
            'user_id' => min($user_id, $recipient_id),
            'recipient_id' => max($user_id, $recipient_id),
            'property_id' => $property_id,
        ]);
    }
    

    public function createConversation(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'property_id' => 'nullable|exists:properties,id',
        ]);

        $user = auth()->user();

        if ($user->id === $request->recipient_id) {
            return $this->respondWithError("You cannot create a conversation with yourself", 400);
        }

        $conversation = $this->getConversation($user->id, $request->recipient_id, $request->property_id);
        $conversation->load(([
            'user:id,first_name,last_name,email,profile_image',
            'recipient:id,first_name,last_name,email,profile_image',
            'property:id,title,location',
        ]));
        
        return $this->respondWithSuccess("Conversation created", $conversation, 201);
    }

    public function getMessages($conversationId)
    {
        $messages = Message::where('conversation_id', $conversationId)
            ->with(['user:first_name,last_name,id', 'property', 'assets'])
            ->orderBy('created_at', 'asc')
            ->get();
        $messages->each(function (Message $message) {
            $message->markAsRead();
        });
        return $this->respondWithSuccess("Fetched messages", $messages);
    }

    public function sendMessage(Request $request)
    {

        $request->validate([
            'content' => 'required|string',
            'property_id' => 'nullable|exists:properties,id',
            'recipient_id' => 'required|exists:users,id',
            'type' => 'required|string|in:text,image,audio',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,mp3,mp4,avi,mov,webm',
        ]);

        $user = auth()->user();

        if ($user->id === $request->recipient_id) {
            return $this->respondWithError("You cannot send a message to yourself", 400);
        }


        if (!$request->input('content') && !$request->hasFile('files')) {
            return $this->respondWithError("Content or files are required", 400);
        }


        $conversation = $this->getConversation($user->id, $request->recipient_id);

        $message = new Message();
        $message->conversation_id = $conversation->id;
        $message->user_id = auth()->id();
        $message->content = $request->input('content');
        $message->property_id = $request->property_id;

        $message->type = $request->input('type');
        $message->save();

        $conversation->last_message_at = now();
        $conversation->save();


        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePath = $file->store('media', 'public');
                $fileType = $file->getClientMimeType();
                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();

                MediaAsset::create([
                    'message_id' => $message->id,
                    'file_path' => $filePath,
                    'file_type' => $fileType,
                    'file_name' => $fileName,
                    'file_size' => $fileSize,
                ]);
            }
        }

        return $this->respondWithSuccess("Sent message", $message, 201);
    }
    public function getConversations(Request $request)
    {
        $userId = auth()->id();
    
        $conversations = Conversation::where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('recipient_id', $userId);
            })
            ->with([
                'user:id,first_name,last_name,email,profile_image',
                'recipient:id,first_name,last_name,email,profile_image',
                'property:id,title,location',
            ])

            ->withCount(['messages as unread_count' => function ($query) use ($userId) {
                $query->where('user_id', '!=', $userId)
                      ->where('read_at', null);
            }])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conversation) use ($userId) {
                $otherUser = $conversation->user_id === $userId 
                    ? $conversation->recipient 
                    : $conversation->user;
                
                return [
                    'id' => $conversation->id,
                    'other_user' => $otherUser,
                    'property' => $conversation->property,
                    'unread_count' => $conversation->unread_count,
                    'last_message_at' => $conversation->last_message_at,
                    'last_message' => $conversation->last_message ,
                    'created_at' => $conversation->created_at,
                ];
            });
    
        return $this->respondWithSuccess("Fetched conversations", $conversations);
    }

    public function createAsset(Request $request, $messageId)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp3,mp4,avi,mov,webm',
            'message_id' => 'required|exists:messages,id',
        ]);

        if (!$request->file) {
            return $this->respondWithError("File is required", 400);
        }

        $file = $request->file('file');
        $filePath = $file->store('media', 'public');
        $fileType = $file->getClientMimeType();
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        $asset = MediaAsset::create([
            'message_id' => $messageId,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_name' => $fileName,
            'file_size' => $fileSize,
        ]);

        return $this->respondWithSuccess("Asset created", $asset, 201);
    }
}
