<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;

class LandingPageController extends Controller
{
    public function index()
    {
        // Statistik untuk Counter Cards
        $stats = [
            'live_jobs'  => Job::count(),
            'companies'  => User::where('role', 'company')->count(),
            'candidates' => User::where('role', 'intern')->count(),
            'new_jobs'   => Job::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        // Kategori dengan jumlah job
        $categories = Category::withCount('jobs')->get();

        // 6 Lowongan Unggulan Terbaru
        $featuredJobs = Job::latest()->take(6)->get();

        return view('welcome', compact('stats', 'categories', 'featuredJobs'));
    }
}