<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContactEnquiryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ContactEnquiry::class);
        if ($request->ajax()) {
            $query = ContactEnquiry::query()->latest('id');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('status', function (ContactEnquiry $enquiry) {
                    $class = $enquiry->status === 'contacted' ? 'success' : 'warning';
                    $text = $enquiry->status === 'contacted' ? 'Contacted' : 'Not Contacted';

                    return '<span class="badge bg-'.$class.'-subtle text-'.$class.'">'.$text.'</span>';
                })
                ->editColumn('created_at', fn (ContactEnquiry $enquiry) => $enquiry->created_at?->format('d M Y h:i A'))
                ->addColumn('action', function (ContactEnquiry $enquiry) {
                    if (! auth()->user()->can('update', $enquiry)) {
                        return '';
                    }

                    return '<button type="button" class="btn btn-sm btn-soft-primary change-status" data-id="'.$enquiry->id.'" data-status="'.e($enquiry->status).'" data-reason="'.e($enquiry->reason).'"><i class="ri-refresh-line me-1"></i>Status</button>';
                })
                ->rawColumns(['status', 'action'])
                ->toJson();
        }

        return view('backend.enquiries.index');
    }

    public function updateStatus(Request $request, ContactEnquiry $enquiry)
    {
        $this->authorize('update', $enquiry);
        $data = $request->validate([
            'status' => ['required', 'in:contacted,not_contacted'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $enquiry->update([
            'status' => $data['status'],
            'reason' => $data['reason'],
            'contacted_at' => $data['status'] === 'contacted' ? now() : null,
        ]);

        return response()->json(['message' => 'Enquiry status updated successfully.']);
    }
}
