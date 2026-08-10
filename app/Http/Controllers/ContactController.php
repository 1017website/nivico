<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'phone' => 'nullable|string|max:30',
            'topic' => 'nullable|string|max:80',
            'message' => 'required|string|max:2000',
        ]);

        $data['name'] = $request->user()->name;
        $data['email'] = $request->user()->email;

        ContactMessage::create($data);

        return back()->with('toast', '✓ Pesan terkirim! Kami akan menghubungi Anda segera.');
    }
}
