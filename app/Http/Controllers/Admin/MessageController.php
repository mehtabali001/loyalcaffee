<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = ContactMessage::query()->latest();
        if ($request->query('filter') === 'unread') {
            $q->whereNull('read_at');
        }
        if ($s = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('message', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }

        return response()->json($q->paginate(25));
    }

    public function markRead(ContactMessage $message): JsonResponse
    {
        $message->update(['read_at' => $message->read_at ? null : now()]);

        return response()->json($message);
    }

    public function destroy(ContactMessage $message): JsonResponse
    {
        $message->delete();

        return response()->json(['ok' => true]);
    }
}
