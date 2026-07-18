<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Enquiries\UpdateEnquiryStatusRequest;
use App\Models\ContactEnquiry;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContactEnquiryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ContactEnquiry::class);
        if ($request->ajax()) {
            $query = ContactEnquiry::query()
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
                ->when($request->filled('from_date'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from_date')))
                ->when($request->filled('to_date'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to_date')))
                ->latest('id');

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

    public function updateStatus(UpdateEnquiryStatusRequest $request, ContactEnquiry $enquiry)
    {
        $data = $request->validated();

        $enquiry->update([
            'status' => $data['status'],
            'reason' => $data['reason'],
            'contacted_at' => $data['status'] === 'contacted' ? now() : null,
        ]);

        return ResponseHelper::success('Enquiry status updated successfully.', ['id' => $enquiry->id]);
    }
}
