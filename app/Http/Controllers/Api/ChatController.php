<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use App\Models\MediaAsset;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends ApiController
{
    public function getMessages(Request $request, $conversationId)
    {
        $messages = Message::where('conversation_id', $conversationId)
            ->with(['user:first_name,last_name,id', 'property', 'assets'])
            ->orderBy('created_at', 'asc')
            ->get();

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


        $conversation = Conversation::firstOrCreate([
            'user_id' => auth()->id(),
            'recipient_id' => $request->recipient_id,
            'property_id' => $request->property_id,
        ]);



        $message = new Message();
        $message->conversation_id = $conversation->id;
        $message->user_id = auth()->id();
        $message->content = $request->input('content');
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
        $conversations = Conversation::where('user_id', auth()->id())
            ->orWhere('recipient_id', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->get();

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
