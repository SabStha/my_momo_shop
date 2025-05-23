<?php $__env->startSection('content'); ?>
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
    html, body {
        margin: 0;
        padding: 0;
        scroll-behavior: smooth;
        font-family: 'Segoe UI', sans-serif;
        background-color: #fff8f0;
    }
    nav {
        background-color: #fff3e0;
        padding: 10px 40px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    nav .nav-wrapper {
        display: flex;
        justify-content: center;
        flex-grow: 1;
    }
    nav a {
        color: #d84315;
        text-decoration: none;
        margin: 0 10px;
        font-weight: 500;
    }
    nav a:hover {
        text-decoration: underline;
    }
    .section-title {
        color: #bf360c;
    }
    .hero-slider {
        text-align: center;
        margin-top: 0;
        position: relative;
    }
    .hero-slider img {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        border-radius: 0 0 10px 10px;
    }
    .order-now {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
    }
    .menu-carousel img {
        width: 100%;
        max-height: 350px;
        object-fit: cover;
        border-radius: 10px;
    }
    .reviews {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
    }
    .review-card {
        background: #fff8f0;
        border-radius: 8px;
        padding: 20px;
        width: 250px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
    }
    .review-card img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
    }
    .bottom-nav {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        gap: 20px;
    }
    .bottom-nav div {
        background: #ffe0b2;
        padding: 20px;
        border-radius: 8px;
        flex: 1 1 250px;
        min-height: 120px;
    }
    .scroll-top, .back-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 99;
        background: #d84315;
        color: #fff;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }
    .back-btn {
        right: auto;
        left: 20px;
    }
    @media(max-width: 768px) {
        nav { flex-direction: column; align-items: flex-start; }
        nav .nav-wrapper { justify-content: flex-start; flex-wrap: wrap; }
        nav a { margin: 5px 10px; }
        .reviews { flex-direction: column; align-items: center; }
        .bottom-nav { flex-direction: column; align-items: center; }
        .order-now { bottom: 15px; }
    }
</style>

<section id="page1">
    <nav class="mb-4">
        <div style="font-weight: bold; font-size: 20px; color: #d84315;">🍲 MOMO SHOP</div>
        <div class="nav-wrapper">
            <a href="#page1">Home</a>
            <a href="#menu">Menu</a>
            <a href="#reviews">Reviews</a>
            <a href="#page2">About</a>
            <a href="#page2">Contact</a>
        </div>
        <div><a href="#">Login/Register</a></div>
    </nav>

    <div class="hero-slider" data-aos="fade-up">
    <div class="carousel slide" id="heroCarousel" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="<?php echo e(asset('storage/products/momo1.jpg')); ?>" class="d-block w-100" alt="Momo 1">
            </div>
            <div class="carousel-item">
                <img src="<?php echo e(asset('storage/products/momo2.jpg')); ?>" class="d-block w-100" alt="Momo 2">
            </div>
            <div class="carousel-item">
                <img src="<?php echo e(asset('storage/products/momo3.jpg')); ?>" class="d-block w-100" alt="Momo 3">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
        <div class="order-now">
            <button class="btn btn-danger btn-lg">Order Now</button>
        </div>
    </div>
</div>

<div class="menu-carousel mt-5 text-center" id="menu" data-aos="zoom-in">
    <h4 class="section-title">Menu Highlights</h4>
    <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="<?php echo e(asset('storage/products/momo4.jpg')); ?>" class="d-block w-100" alt="Momo 4">
            </div>
            <div class="carousel-item">
                <img src="<?php echo e(asset('storage/products/momo5.jpg')); ?>" class="d-block w-100" alt="Momo 5">
            </div>
            <div class="carousel-item">
                <img src="<?php echo e(asset('storage/products/momo6.jpg')); ?>" class="d-block w-100" alt="Momo 6">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<div class="reviews mt-5" id="reviews">
    <div class="review-card" data-aos="fade-right">
        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="customer">
        <h6>Sita Rai</h6>
        <p>"Best momo in town! Love the spicy sauce!"</p>
        <p>⭐⭐⭐⭐⭐</p>
    </div>
    <div class="review-card" data-aos="fade-up">
        <img src="https://randomuser.me/api/portraits/men/35.jpg" alt="customer">
        <h6>Ram Shrestha</h6>
        <p>"Highly recommended! Customizable momos are a game changer."</p>
        <p>⭐⭐⭐⭐⭐</p>
    </div>
    <div class="review-card" data-aos="fade-left">
        <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="customer">
        <h6>Laxmi Gurung</h6>
        <p>"So fresh and tasty. I order every weekend!"</p>
        <p>⭐⭐⭐⭐⭐</p>
    </div>
</div>
</section>

<section id="page2">
    <div class="mb-4 text-center" data-aos="fade-up">
        <h4 class="section-title">About Us</h4>
        <p>We started our momo journey in 2024 with one goal: deliver hot, tasty, customizable momos to your door.</p>
        <img src="<?php echo e(asset('storage/products/hotel.jpg')); ?>" class="d-block w-100" >
    </div>

    <div class="bottom-nav mt-5">
        <div data-aos="fade-up"><strong>Address:</strong><br>Kathmandu, Nepal</div>
        <div data-aos="fade-up"><strong>How to Order / FAQs:</strong><br>1. Choose your momo<br>2. Customize it<br>3. Checkout & enjoy</div>
        <div data-aos="fade-up"><strong>Contact:</strong><br>Phone: +977-9812345678</div>
    </div>
</section>

<a href="#page1" class="scroll-top">Top</a>
<a href="javascript:history.back()" class="back-btn">Back</a>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init();
</script>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/home.blade.php ENDPATH**/ ?>