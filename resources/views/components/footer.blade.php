<footer style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; padding: 40px 0 20px; margin-top: 60px;">
    <div class="container-fluid">
        <div class="row py-5">
            <!-- About Section -->
            <div class="col-md-3 mb-4">
                <h5 style="font-weight: 700; color: white; margin-bottom: 15px;">
                    <i class="bi bi-book"></i> Campus Exchange
                </h5>
                <p class="small">
                    A peer-to-peer resource sharing platform for college students to lend, borrow, and sell academic resources.
                </p>
                <div class="mt-3">
                    <a href="#" class="me-3" style="color: rgba(255,255,255,0.85);"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="me-3" style="color: rgba(255,255,255,0.85);"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="me-3" style="color: rgba(255,255,255,0.85);"><i class="bi bi-instagram"></i></a>
                    <a href="#" style="color: rgba(255,255,255,0.85);"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-md-3 mb-4">
                <h5 style="font-weight: 700; color: white; margin-bottom: 15px;">Quick Links</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <a href="{{ route('frontend.items.index') }}" style="color: rgba(255,255,255,0.85); text-decoration: none;">Browse Items</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('frontend.search.index') }}" style="color: rgba(255,255,255,0.85); text-decoration: none;">Search</a>
                    </li>
                    @auth
                        <li class="mb-2">
                            <a href="{{ route('frontend.dashboard') }}" style="color: rgba(255,255,255,0.85); text-decoration: none;">Dashboard</a>
                        </li>
                    @else
                        <li class="mb-2">
                            <a href="{{ route('login') }}" style="color: rgba(255,255,255,0.85); text-decoration: none;">Login</a>
                        </li>
                    @endauth
                </ul>
            </div>

            <!-- Resources -->
            <div class="col-md-3 mb-4">
                <h5 style="font-weight: 700; color: white; margin-bottom: 15px;">Resources</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <a href="#" style="color: rgba(255,255,255,0.85); text-decoration: none;">How It Works</a>
                    </li>
                    <li class="mb-2">
                        <a href="#" style="color: rgba(255,255,255,0.85); text-decoration: none;">Safety Tips</a>
                    </li>
                    <li class="mb-2">
                        <a href="#" style="color: rgba(255,255,255,0.85); text-decoration: none;">FAQ</a>
                    </li>
                    <li class="mb-2">
                        <a href="#" style="color: rgba(255,255,255,0.85); text-decoration: none;">Contact Us</a>
                    </li>
                </ul>
            </div>

            <!-- Support -->
            <div class="col-md-3 mb-4">
                <h5 style="font-weight: 700; color: white; margin-bottom: 15px;">Support</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <a href="#" style="color: rgba(255,255,255,0.85); text-decoration: none;">Privacy Policy</a>
                    </li>
                    <li class="mb-2">
                        <a href="#" style="color: rgba(255,255,255,0.85); text-decoration: none;">Terms of Service</a>
                    </li>
                    <li class="mb-2">
                        <a href="#" style="color: rgba(255,255,255,0.85); text-decoration: none;">Report Issue</a>
                    </li>
                    <li class="mb-2">
                        <a href="#" style="color: rgba(255,255,255,0.85); text-decoration: none;">Community Guidelines</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div style="border-top: 1px solid rgba(255,255,255,0.2); margin-top: 20px; padding-top: 20px;">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="small mb-0">
                        &copy; {{ date('Y') }} Campus Resource Exchange. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="small mb-0">
                        Made with <i class="bi bi-heart-fill" style="color: #ff6b6b;"></i> for students
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>
