<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentRequest;
use App\Models\Article;
use App\Models\Chamber;
use App\Models\DoctorProfile;
use App\Models\Inquiry;
use App\Models\SiteSetting;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'newAppointments' => AppointmentRequest::where('status', 'new')->count(),
            'newInquiries' => Inquiry::where('status', 'new')->count(),
            'activeChambers' => Chamber::where('is_active', true)->count(),
            'articleCount' => Article::where('status', 'published')->count(),
            'recentRequests' => AppointmentRequest::with('chamber')->latest()->limit(5)->get(),
            'profile' => DoctorProfile::current(),
            'settings' => SiteSetting::current(),
        ]);
    }
}
