@extends('layouts.app')

@section('title', 'Family Dashboard')

<?php
    $pageClass = 'page-family-tree page-dashboard';
?>

@section('styles')
<style>
    .dashboard-container {
        max-width: 1100px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .welcome-section {
        background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
        padding: 60px;
        border-radius: 30px;
        color: white;
        margin-bottom: 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 20px 40px rgba(49, 130, 206, 0.2);
    }

    .welcome-text h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 10px; }
    .welcome-text p { font-size: 1.1rem; opacity: 0.9; }

    .btn-tree {
        background: white;
        color: #3182ce;
        padding: 18px 36px;
        border-radius: 16px;
        font-weight: 800;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .btn-tree:hover { transform: scale(1.05); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
    }

    .dashboard-card {
        background: white;
        padding: 30px;
        border-radius: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        border: 1px solid #edf2f7;
    }

    .card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        border-bottom: 1px solid #f7fafc;
        padding-bottom: 15px;
    }

    .card-header h3 { font-weight: 800; color: #2d3748; }

    .birthday-item {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .birthday-img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #ebf8ff; }
    
    .event-item {
        padding: 15px;
        background: #f8fafc;
        border-radius: 12px;
        margin-bottom: 12px;
        border-left: 4px solid #3182ce;
    }

</style>
@endsection

@section('content')
<div class="dashboard-container">
    <div style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #f7fafc; padding-bottom: 30px;">
        <div>
            <h1 style="font-family: 'Sora', sans-serif; font-size: 2.5rem; font-weight: 800; color: #1a365d; margin: 0;">Halo, {{ session('authenticated_user.username') }}!</h1>
            <p style="font-size: 1.1rem; color: #718096; margin-top: 10px;">Selamat datang di sistem manajemen keluarga besar Anda.</p>
        </div>
        <a href="/tree" style="text-decoration: none;">
            <button class="btn btn-primary" type="button" style="padding: 15px 30px; border-radius: 12px; display: flex; align-items: center; gap: 10px; font-weight: 700;">
                <i data-lucide="network" style="width: 20px;"></i>
                Open Family Tree
            </button>
        </a>
    </div>

    <div class="stats-grid">
        <!-- Birthday Section -->
        <div class="dashboard-card">
            <div class="card-header">
                <i data-lucide="cake" style="color: #f6ad55;"></i>
                <h3>Today's Birthdays</h3>
            </div>
            @forelse($birthdays as $b)
                <div class="birthday-item">
                    <img src="{{ $b->picture }}" class="birthday-img">
                    <div>
                        <div style="font-weight: 700; color: #2d3748;">{{ $b->name }}</div>
                        <div style="font-size: 0.85rem; color: #718096;">Happy Birthday! 🎂</div>
                    </div>
                </div>
            @empty
                <p style="color: #a0aec0; text-align: center; padding: 20px;">No birthdays today.</p>
            @endforelse
        </div>

        <!-- Upcoming Events -->
        <div class="dashboard-card">
            <div class="card-header">
                <i data-lucide="calendar" style="color: #3182ce;"></i>
                <h3>Upcoming Events</h3>
            </div>
            @forelse($upcomingEvents as $e)
                <div class="event-item">
                    <div style="font-weight: 800; color: #2d3748; margin-bottom: 4px;">{{ $e->title }}</div>
                    <div style="font-size: 0.85rem; color: #4a5568;">📅 {{ date('d M Y, H:i', strtotime($e->event_date)) }}</div>
                    <div style="font-size: 0.85rem; color: #718096;">📍 {{ $e->location ?: 'No location' }}</div>
                </div>
            @empty
                <p style="color: #a0aec0; text-align: center; padding: 20px;">No upcoming events.</p>
            @endforelse
            <a href="/events" style="text-decoration: none; display: block; text-align: center; margin-top: 15px; color: #3182ce; font-weight: 700;">View All Events</a>
        </div>

        <!-- System Stats -->
        <div class="dashboard-card">
            <div class="card-header">
                <i data-lucide="users" style="color: #38a169;"></i>
                <h3>Family Stats</h3>
            </div>
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 3rem; font-weight: 900; color: #2d3748;">{{ $totalMembers }}</div>
                <div style="color: #718096; font-weight: 600;">Registered Members</div>
                <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <a href="/calendar" style="text-decoration: none;"><button class="btn btn-soft btn-block" type="button">Calendar</button></a>
                    <a href="/chatbot" style="text-decoration: none;"><button class="btn btn-soft btn-block" type="button">AI Chat</button></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
