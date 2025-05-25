@extends('layouts.app')

@push('styles')
<style>
    .section-title {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    .about-values .text-center {
        padding: 2rem 1rem;
        transition: transform 0.3s ease;
    }
    .about-values .text-center:hover {
        transform: translateY(-5px);
    }
    .product-category {
        padding: 1.5rem;
        transition: transform 0.3s ease;
    }
    .product-category:hover {
        transform: translateY(-5px);
    }
    .product-category img {
        height: 200px;
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="contact-us container">
        <div class="mw-930">
            <h2 class="page-title">About HOMMSS</h2>
        </div>
    </section>

    <hr class="mt-2 text-secondary" />
    <div class="mb-4 pb-4"></div>

    <section class="about-us container">
        <div class="row">
            <div class="col-lg-6">
                <h2 class="section-title">Our Story</h2>
                <p class="fs-6 text-secondary">
                    HOMMSS Corporation has been a trusted name in the construction and home improvement industry for years.
                    We specialize in providing high-quality tiles, sanitary ware, kitchen sinks, and natural granite
                    products to homeowners, contractors, and businesses across the Philippines.
                </p>
                <p class="fs-6 text-secondary">
                    Located in Sterling Industrial Park, Libtong, Meycauayan Bulacan, our company is committed to
                    delivering excellence in both product quality and customer service. We understand that your home
                    is your sanctuary, and we're here to help you create beautiful, functional spaces that reflect
                    your personal style.
                </p>
            </div>
            <div class="col-lg-6">
                <img loading="lazy" src="{{ asset('assets/images/home/hommss/wall-tiles.png') }}"
                     alt="HOMMSS Showroom" class="w-100 h-auto rounded">
            </div>
        </div>
    </section>

    <div class="mb-5 pb-4"></div>

    <section class="about-values container">
        <div class="row">
            <div class="col-12">
                <h2 class="section-title text-center mb-5">Our Values</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="text-center">
                    <div class="mb-3">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                            <path d="M9 12l2 2 4-4"></path>
                            <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path>
                            <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path>
                            <path d="M12 3c0 1-1 3-3 3s-3-2-3-3 1-3 3-3 3 2 3 3"></path>
                            <path d="M12 21c0-1 1-3 3-3s3 2 3 3-1 3-3 3-3-2-3-3"></path>
                        </svg>
                    </div>
                    <h4>Quality Assurance</h4>
                    <p class="text-secondary">
                        We source only the finest materials and work with trusted manufacturers to ensure
                        every product meets our high standards of quality and durability.
                    </p>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="text-center">
                    <div class="mb-3">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    </div>
                    <h4>Customer Satisfaction</h4>
                    <p class="text-secondary">
                        Your satisfaction is our priority. We provide personalized service, expert advice,
                        and comprehensive support throughout your project journey.
                    </p>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="text-center">
                    <div class="mb-3">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12,6 12,12 16,14"></polyline>
                        </svg>
                    </div>
                    <h4>Timely Delivery</h4>
                    <p class="text-secondary">
                        We understand the importance of project timelines. Our efficient logistics ensure
                        your orders are processed and delivered promptly.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="mb-5 pb-4"></div>

    <section class="about-products bg-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="section-title text-center mb-5">Our Product Range</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-category text-center">
                        <img loading="lazy" src="{{ asset('assets/images/products/walltiles.png') }}"
                             alt="Wall Tiles" class="w-100 h-auto rounded mb-3">
                        <h5>Wall & Floor Tiles</h5>
                        <p class="text-secondary">Premium ceramic, porcelain, and natural stone tiles for every space.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-category text-center">
                        <img loading="lazy" src="{{ asset('assets/images/products/sink.png') }}"
                             alt="Kitchen Sinks" class="w-100 h-auto rounded mb-3">
                        <h5>Kitchen Sinks</h5>
                        <p class="text-secondary">Durable and stylish kitchen sinks in various materials and designs.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-category text-center">
                        <img loading="lazy" src="{{ asset('assets/images/home/hommss/sanitary-ware.png') }}"
                             alt="Sanitary Ware" class="w-100 h-auto rounded mb-3">
                        <h5>Sanitary Ware</h5>
                        <p class="text-secondary">Complete bathroom solutions including toilets, basins, and fixtures.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-category text-center">
                        <img loading="lazy" src="{{ asset('assets/images/home/hommss/natural-granite.png') }}"
                             alt="Natural Granite" class="w-100 h-auto rounded mb-3">
                        <h5>Natural Granite</h5>
                        <p class="text-secondary">Beautiful natural granite slabs for countertops and architectural features.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="mb-5 pb-4"></div>

    <section class="about-contact container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title">Visit Our Showroom</h2>
                <p class="fs-6 text-secondary mb-4">
                    Experience our products firsthand at our showroom in Meycauayan, Bulacan.
                    Our knowledgeable staff is ready to help you find the perfect solutions for your project.
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <h5>Address</h5>
                        <p class="text-secondary">
                            Blk1 Lot 1 Ph6 Glocal St.<br>
                            Sterling Industrial Park<br>
                            Libtong, Meycauayan Bulacan<br>
                            Philippines 3020
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h5>Contact Information</h5>
                        <p class="text-secondary">
                            <strong>Phone:</strong> (044) 816 7442<br>
                            <strong>Email:</strong> hommss@gmail.com<br>
                            <strong>Facebook:</strong> @hommss.tiles
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('contact') }}" class="btn btn-primary btn-lg">Get In Touch</a>
                    <a href="{{ route('shop.index') }}" class="btn btn-outline-primary btn-lg ms-3">Browse Products</a>
                </div>
            </div>
        </div>
    </section>

    <div class="mb-5 pb-4"></div>
</main>
@endsection
