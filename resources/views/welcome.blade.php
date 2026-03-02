@extends('layouts.app')

@section('title', 'Home - Campus Resource Exchange')

@section('content')

{{-- ================================================
     HERO SECTION
     ================================================ --}}
<div class="row align-items-center">

    <!-- Hero Left -->
    <div class="col-lg-6 mb-5 mb-lg-0">
        <div style="padding: 20px;">
            <h1 class="page-title" style="font-size: 3rem; margin-bottom: 20px;">
                Share Knowledge, Save Money
            </h1>
            <p style="font-size: 1.2rem; color: #666; margin-bottom: 30px; line-height: 1.8;">
                Campus Resource Exchange is a peer-to-peer platform where students lend, borrow, and sell academic resources. From textbooks to lab equipment, connect with your campus community.
            </p>

            <div class="d-flex gap-3 flex-wrap">
                @auth
                    @if (auth()->user()->isSuperAdmin())
                        <a href="{{ route('super-admin.dashboard') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-speedometer2"></i> Super Admin Dashboard
                        </a>
                    @elseif (auth()->user()->isUniAdmin())
                        <a href="{{ route('uni-admin.dashboard') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-building"></i> University Dashboard
                        </a>
                    @else
                        <a href="{{ route('frontend.items.index') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-bag"></i> Browse Items
                        </a>
                        <a href="{{ route('frontend.items.create') }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-plus-circle"></i> List Item
                        </a>
                    @endif
                @else
                    <a href="{{ route('frontend.items.index') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-bag"></i> Browse Items
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Get Started Free
                    </a>
                @endauth
            </div>

            <!-- Live Stats -->
            <div style="margin-top: 40px; display: flex; gap: 30px; flex-wrap: wrap;">
                <div>
                    <h4 style="color: #0d6efd; font-weight: 700;">{{ $stats['total_items'] ?? '—' }}</h4>
                    <p style="color: #666; margin: 0;">Items Listed</p>
                </div>
                <div>
                    <h4 style="color: #0d6efd; font-weight: 700;">{{ $stats['total_users'] ?? '—' }}</h4>
                    <p style="color: #666; margin: 0;">Active Users</p>
                </div>
                <div>
                    <h4 style="color: #0d6efd; font-weight: 700;">{{ $stats['total_universities'] ?? '—' }}</h4>
                    <p style="color: #666; margin: 0;">Universities</p>
                </div>
                <div>
                    <h4 style="color: #0d6efd; font-weight: 700;">{{ $stats['avg_rating'] ?? '—' }}★</h4>
                    <p style="color: #666; margin: 0;">Avg Rating</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Right -->
    <div class="col-lg-6">
        <div style="background: linear-gradient(135deg, #e7f1ff 0%, #f0f4ff 100%); border-radius: 12px; padding: 40px; text-align: center; min-height: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <i class="bi bi-bag-check" style="font-size: 6rem; color: #0d6efd; margin-bottom: 20px;"></i>
            <h3 style="color: #0d6efd; font-weight: 700;">Lend • Borrow • Sell</h3>
            <p style="color: #666; margin-bottom: 0;">Connect with your campus community and access resources when you need them.</p>
        </div>
    </div>
</div>


{{-- ================================================
     HOW IT WORKS
     ================================================ --}}
<div style="margin-top: 80px;">
    <h2 style="text-align: center; color: #0d6efd; font-weight: 700; margin-bottom: 10px; font-size: 2.2rem;">
        How It Works
    </h2>
    <p style="text-align: center; color: #666; margin-bottom: 50px;">Three simple steps to start sharing resources on your campus.</p>

    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <div style="background: #e7f1ff; width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="bi bi-person-plus" style="font-size: 1.8rem; color: #0d6efd;"></i>
            </div>
            <h5 style="font-weight: 700; color: #333;">1. Register with Your University Email</h5>
            <p style="color: #666;">Sign up using your official university email. Your account is verified by your university admin to ensure a trusted community.</p>
        </div>
        <div class="col-md-4 mb-4">
            <div style="background: #e7f1ff; width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="bi bi-box-seam" style="font-size: 1.8rem; color: #0d6efd;"></i>
            </div>
            <h5 style="font-weight: 700; color: #333;">2. List or Find Resources</h5>
            <p style="color: #666;">List items you want to lend or sell, or browse items listed by students at your university. Textbooks, equipment, notes — anything academic.</p>
        </div>
        <div class="col-md-4 mb-4">
            <div style="background: #e7f1ff; width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="bi bi-chat-dots" style="font-size: 1.8rem; color: #0d6efd;"></i>
            </div>
            <h5 style="font-weight: 700; color: #333;">3. Connect & Exchange</h5>
            <p style="color: #666;">Message the other student directly to coordinate pickup. Rate each other after the exchange to build a trustworthy campus community.</p>
        </div>
    </div>
</div>


{{-- ================================================
     FEATURES
     ================================================ --}}
<div style="margin-top: 80px;">
    <h2 style="text-align: center; color: #0d6efd; font-weight: 700; margin-bottom: 50px; font-size: 2.2rem;">
        Why Choose Campus Exchange?
    </h2>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card" style="border: none; text-align: center;">
                <div class="card-body" style="padding: 30px;">
                    <i class="bi bi-shield-check" style="font-size: 3rem; color: #0d6efd; margin-bottom: 15px; display: block;"></i>
                    <h5 style="color: #333; font-weight: 700; margin-bottom: 15px;">Safe & Verified</h5>
                    <p style="color: #666; margin: 0;">University email verification ensures you're connecting only with real, verified students from your campus.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card" style="border: none; text-align: center;">
                <div class="card-body" style="padding: 30px;">
                    <i class="bi bi-star" style="font-size: 3rem; color: #0d6efd; margin-bottom: 15px; display: block;"></i>
                    <h5 style="color: #333; font-weight: 700; margin-bottom: 15px;">Trusted Ratings</h5>
                    <p style="color: #666; margin: 0;">Peer reviews and ratings help you make informed decisions about who to borrow from or sell to.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card" style="border: none; text-align: center;">
                <div class="card-body" style="padding: 30px;">
                    <i class="bi bi-chat-dots" style="font-size: 3rem; color: #0d6efd; margin-bottom: 15px; display: block;"></i>
                    <h5 style="color: #333; font-weight: 700; margin-bottom: 15px;">Direct Messaging</h5>
                    <p style="color: #666; margin: 0;">Communicate directly with other students to coordinate exchanges and answer questions.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 10px;">
        <div class="col-md-4 mb-4">
            <div class="card" style="border: none; text-align: center;">
                <div class="card-body" style="padding: 30px;">
                    <i class="bi bi-calendar-check" style="font-size: 3rem; color: #0d6efd; margin-bottom: 15px; display: block;"></i>
                    <h5 style="color: #333; font-weight: 700; margin-bottom: 15px;">Flexible Lending</h5>
                    <p style="color: #666; margin: 0;">Set your own lending duration. List items to lend, sell, or both — you stay in control.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card" style="border: none; text-align: center;">
                <div class="card-body" style="padding: 30px;">
                    <i class="bi bi-cash-coin" style="font-size: 3rem; color: #0d6efd; margin-bottom: 15px; display: block;"></i>
                    <h5 style="color: #333; font-weight: 700; margin-bottom: 15px;">Save Money</h5>
                    <p style="color: #666; margin: 0;">Borrow expensive textbooks and equipment instead of buying them. Save hundreds every semester.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card" style="border: none; text-align: center;">
                <div class="card-body" style="padding: 30px;">
                    <i class="bi bi-people" style="font-size: 3rem; color: #0d6efd; margin-bottom: 15px; display: block;"></i>
                    <h5 style="color: #333; font-weight: 700; margin-bottom: 15px;">Campus Community</h5>
                    <p style="color: #666; margin: 0;">Build a network with students at your college. Help each other succeed academically.</p>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ================================================
     UNIVERSITY APPLICATION SECTION
     Only shown to guests.
     ================================================ --}}
@guest
<div style="margin-top: 80px;">
    <div class="row align-items-center" style="background: linear-gradient(135deg, #f8f9ff 0%, #e7f1ff 100%); border-radius: 16px; padding: 60px 40px;">

        <!-- Left: Text -->
        <div class="col-lg-7 mb-4 mb-lg-0">
            <span style="background: #0d6efd; color: white; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; padding: 4px 12px; border-radius: 20px; display: inline-block; margin-bottom: 16px;">
                For University Representatives
            </span>
            <h2 style="color: #1a1a2e; font-weight: 700; font-size: 2rem; margin-bottom: 16px;">
                Bring Campus Exchange to Your University
            </h2>
            <p style="color: #555; font-size: 1.05rem; line-height: 1.8; margin-bottom: 24px;">
                Is your university not on the platform yet? Submit an application to register your institution. Once approved by our team, your students can immediately start sharing resources with each other.
            </p>

            <!-- Steps -->
            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 30px;">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="background: #0d6efd; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.8rem; font-weight: 700;">1</div>
                    <p style="margin: 0; color: #555;">Submit your university details and your contact information via the application form.</p>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="background: #0d6efd; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.8rem; font-weight: 700;">2</div>
                    <p style="margin: 0; color: #555;">Our super admin reviews and approves your application, then issues your university admin login credentials.</p>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="background: #0d6efd; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.8rem; font-weight: 700;">3</div>
                    <p style="margin: 0; color: #555;">Log in as university admin and start verifying students. Your campus community goes live immediately.</p>
                </div>
            </div>

            <div class="d-flex gap-3 flex-wrap">
                <a href="{{ route('university.apply') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-building-add me-1"></i> Apply to Register Your University
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">
                    <i class="bi bi-box-arrow-in-right me-1"></i> University Admin Login
                </a>
            </div>
        </div>

        <!-- Right: Requirements card -->
        <div class="col-lg-5">
            <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <h5 style="font-weight: 700; color: #0d6efd; margin-bottom: 20px;">
                    <i class="bi bi-info-circle me-2"></i>What You'll Need to Apply
                </h5>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                    <li style="display: flex; align-items: center; gap: 10px; color: #555;">
                        <i class="bi bi-check-circle-fill" style="color: #0d6efd; flex-shrink: 0;"></i>
                        Official university name and location (state, city)
                    </li>
                    <li style="display: flex; align-items: center; gap: 10px; color: #555;">
                        <i class="bi bi-check-circle-fill" style="color: #0d6efd; flex-shrink: 0;"></i>
                        Official student email domain (e.g. <code>university.edu.bd</code>)
                    </li>
                    <li style="display: flex; align-items: center; gap: 10px; color: #555;">
                        <i class="bi bi-check-circle-fill" style="color: #0d6efd; flex-shrink: 0;"></i>
                        Your name, email and phone as the applicant
                    </li>
                    <li style="display: flex; align-items: center; gap: 10px; color: #555;">
                        <i class="bi bi-check-circle-fill" style="color: #0d6efd; flex-shrink: 0;"></i>
                        Brief description of your university (optional)
                    </li>
                </ul>

                <hr style="margin: 24px 0; border-color: #e9ecef;">

                <p style="color: #888; font-size: 0.88rem; margin: 0;">
                    <i class="bi bi-clock me-1"></i>
                    Applications are typically reviewed within 1–2 business days. You'll receive your admin credentials by email upon approval.
                </p>
            </div>
        </div>

    </div>
</div>


{{-- ================================================
     STUDENT CTA — guests only
     ================================================ --}}
<div style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; border-radius: 12px; padding: 60px 30px; text-align: center; margin-top: 50px;">
    <h2 style="font-weight: 700; margin-bottom: 15px; font-size: 2rem;">Ready to Get Started?</h2>
    <p style="font-size: 1.1rem; margin-bottom: 30px; opacity: 0.9;">
        Join your campus community on Campus Exchange. Register with your university email and start sharing today.
    </p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="{{ route('register') }}" class="btn btn-light btn-lg" style="font-weight: 600;">
            <i class="bi bi-person-plus me-1"></i> Create Student Account
        </a>
        <a href="{{ route('frontend.items.index') }}" class="btn btn-outline-light btn-lg" style="font-weight: 600;">
            <i class="bi bi-bag me-1"></i> Browse Items First
        </a>
    </div>
</div>
@endguest

@endsection