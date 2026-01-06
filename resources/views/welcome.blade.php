@extends('layouts.app')

@section('title', 'Home - Campus Resource Exchange')

@section('content')
<div class="row align-items-center">
    <!-- Hero Section Left -->
    <div class="col-lg-6 mb-5 mb-lg-0">
        <div style="padding: 20px;">
            <h1 class="page-title" style="font-size: 3rem; margin-bottom: 20px;">
                Share Knowledge, Save Money
            </h1>
            <p style="font-size: 1.2rem; color: #666; margin-bottom: 30px; line-height: 1.8;">
                Campus Resource Exchange is a peer-to-peer platform where students lend, borrow, and sell academic resources. From textbooks to lab equipment, connect with your campus community.
            </p>

            <div class="d-flex gap-3">
                @auth
                    <a href="{{ route('frontend.items.index') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-bag"></i> Browse Items
                    </a>
                    <a href="{{ route('frontend.items.create') }}" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-plus-circle"></i> List Item
                    </a>
                @else
                    <a href="{{ route('frontend.items.index') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-bag"></i> Browse Items
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Get Started
                    </a>
                @endauth
            </div>

            <div style="margin-top: 40px; display: flex; gap: 30px;">
                <div>
                    <h4 style="color: #0d6efd; font-weight: 700;">500+</h4>
                    <p style="color: #666; margin: 0;">Items Listed</p>
                </div>
                <div>
                    <h4 style="color: #0d6efd; font-weight: 700;">1000+</h4>
                    <p style="color: #666; margin: 0;">Active Users</p>
                </div>
                <div>
                    <h4 style="color: #0d6efd; font-weight: 700;">4.8★</h4>
                    <p style="color: #666; margin: 0;">Avg Rating</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Section Right -->
    <div class="col-lg-6">
        <div style="background: linear-gradient(135deg, #e7f1ff 0%, #f0f4ff 100%); border-radius: 12px; padding: 40px; text-align: center; min-height: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <i class="bi bi-bag-check" style="font-size: 6rem; color: #0d6efd; margin-bottom: 20px;"></i>
            <h3 style="color: #0d6efd; font-weight: 700;">Lend • Borrow • Sell</h3>
            <p style="color: #666; margin-bottom: 0;">Connect with your campus community and access resources when you need them.</p>
        </div>
    </div>
</div>

<!-- Features Section -->
<div style="margin-top: 80px;">
    <h2 style="text-align: center; color: #0d6efd; font-weight: 700; margin-bottom: 50px; font-size: 2.5rem;">
        Why Choose Campus Exchange?
    </h2>

    <div class="row">
        <!-- Feature 1 -->
        <div class="col-md-4 mb-4">
            <div class="card" style="border: none; text-align: center;">
                <div class="card-body" style="padding: 30px;">
                    <i class="bi bi-shield-check" style="font-size: 3rem; color: #0d6efd; margin-bottom: 15px; display: block;"></i>
                    <h5 style="color: #333; font-weight: 700; margin-bottom: 15px;">Safe & Secure</h5>
                    <p style="color: #666; margin: 0;">College email verification ensures you're connecting with real students on campus.</p>
                </div>
            </div>
        </div>

        <!-- Feature 2 -->
        <div class="col-md-4 mb-4">
            <div class="card" style="border: none; text-align: center;">
                <div class="card-body" style="padding: 30px;">
                    <i class="bi bi-star" style="font-size: 3rem; color: #0d6efd; margin-bottom: 15px; display: block;"></i>
                    <h5 style="color: #333; font-weight: 700; margin-bottom: 15px;">Trusted Ratings</h5>
                    <p style="color: #666; margin: 0;">Peer reviews and ratings help you make informed decisions about what to borrow or buy.</p>
                </div>
            </div>
        </div>

        <!-- Feature 3 -->
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

    <div class="row" style="margin-top: 30px;">
        <!-- Feature 4 -->
        <div class="col-md-4 mb-4">
            <div class="card" style="border: none; text-align: center;">
                <div class="card-body" style="padding: 30px;">
                    <i class="bi bi-calendar-check" style="font-size: 3rem; color: #0d6efd; margin-bottom: 15px; display: block;"></i>
                    <h5 style="color: #333; font-weight: 700; margin-bottom: 15px;">Flexible Lending</h5>
                    <p style="color: #666; margin: 0;">Set your own lending duration and dates. Items lend, sell, or both—complete control.</p>
                </div>
            </div>
        </div>

        <!-- Feature 5 -->
        <div class="col-md-4 mb-4">
            <div class="card" style="border: none; text-align: center;">
                <div class="card-body" style="padding: 30px;">
                    <i class="bi bi-cash-coin" style="font-size: 3rem; color: #0d6efd; margin-bottom: 15px; display: block;"></i>
                    <h5 style="color: #333; font-weight: 700; margin-bottom: 15px;">Save Money</h5>
                    <p style="color: #666; margin: 0;">Get expensive textbooks and equipment at a fraction of retail prices.</p>
                </div>
            </div>
        </div>

        <!-- Feature 6 -->
        <div class="col-md-4 mb-4">
            <div class="card" style="border: none; text-align: center;">
                <div class="card-body" style="padding: 30px;">
                    <i class="bi bi-people" style="font-size: 3rem; color: #0d6efd; margin-bottom: 15px; display: block;"></i>
                    <h5 style="color: #333; font-weight: 700; margin-bottom: 15px;">Community</h5>
                    <p style="color: #666; margin: 0;">Build a network with students in your college and help each other succeed.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
@if (!auth()->check())
    <div style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; border-radius: 12px; padding: 60px 30px; text-align: center; margin-top: 80px;">
        <h2 style="font-weight: 700; margin-bottom: 15px; font-size: 2rem;">Ready to Get Started?</h2>
        <p style="font-size: 1.1rem; margin-bottom: 30px;">Join thousands of students sharing resources on Campus Exchange.</p>
        <a href="{{ route('register') }}" class="btn btn-light btn-lg" style="font-weight: 600;">
            Create Your Account
        </a>
    </div>
@endif

@endsection
