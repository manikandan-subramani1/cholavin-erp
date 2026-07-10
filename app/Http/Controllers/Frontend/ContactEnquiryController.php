<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use Illuminate\Http\Request;

class ContactEnquiryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'service' => ['nullable', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        ContactEnquiry::create($data);

        if ($request->ajax()) {
            return response()->json(['message' => 'Your enquiry has been submitted. We will contact you shortly.']);
        }

        return back()->with('success', 'Your enquiry has been submitted.');
    }
}
