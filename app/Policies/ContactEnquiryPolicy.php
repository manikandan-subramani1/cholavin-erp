<?php

namespace App\Policies;

use App\Models\ContactEnquiry;
use App\Models\User;

class ContactEnquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('enquiries.view');
    }

    public function update(User $user, ContactEnquiry $enquiry): bool
    {
        return $user->hasPermission('enquiries.update');
    }
}
