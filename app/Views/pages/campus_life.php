<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">Campus Life</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <span>Campus Life</span>
        </div>
    </div>
</section>

<!-- Intro Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">Experience</span>
            <h2 class="section-header__title">A Vibrant Community</h2>
            <p class="section-header__description">
                Life at our university extends far beyond the classroom. Discover a thriving 
                campus culture with diverse activities, organizations, and opportunities for growth.
            </p>
        </div>
    </div>
</section>

<!-- Student Life -->
<section class="section section-light">
    <div class="container">
        <div class="feature-section">
            <div class="feature-section__image animate-on-scroll">
                <img src="<?= base_url('assets/images/student_activities.png') ?>" alt="Student Activities">
            </div>
            <div class="feature-section__content animate-on-scroll">
                <span class="feature-section__subtitle">Student Life</span>
                <h2 class="feature-section__title">Find Your Community</h2>
                <p class="feature-section__description">
                    With over 600 student organizations, there's something for everyone. 
                    Whether you're passionate about arts, culture, service, or athletics, 
                    you'll find your place here.
                </p>
                <ul class="feature-list">
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>600+ Student Organizations</span>
                    </li>
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>Cultural Centers</span>
                    </li>
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>Leadership Programs</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Housing & Dining -->
<section class="section">
    <div class="container">
        <div class="feature-section feature-section--reverse">
            <div class="feature-section__image animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=600&h=400&fit=crop" alt="Campus Housing">
            </div>
            <div class="feature-section__content animate-on-scroll">
                <span class="feature-section__subtitle">Living on Campus</span>
                <h2 class="feature-section__title">Housing & Dining</h2>
                <p class="feature-section__description">
                    Experience the convenience and community of campus living. Our residential 
                    communities are designed to foster connections and support your academic success.
                </p>
                <ul class="feature-list">
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>80+ Residence Halls</span>
                    </li>
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>30+ Dining Options</span>
                    </li>
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>Living-Learning Communities</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Recreation -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">Recreation & Wellness</span>
            <h2 class="section-header__title">Stay Active & Healthy</h2>
        </div>
        
        <div class="grid grid-3">
            <div class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=400&h=250&fit=crop" alt="Fitness Center" class="card__image">
                <div class="card__content">
                    <h3 class="card__title">Fitness Centers</h3>
                    <p class="card__excerpt">
                        State-of-the-art facilities with equipment, classes, and personal training.
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1461896836934- voices-of-the-voices-06d9d1f3c?w=400&h=250&fit=crop" alt="Outdoor Activities" class="card__image">
                <div class="card__content">
                    <h3 class="card__title">Outdoor Adventures</h3>
                    <p class="card__excerpt">
                        Explore hiking, camping, kayaking, and more with our outdoor programs.
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <img src="<?= base_url('assets/images/wellness_recreation.png') ?>" alt="Wellness" class="card__image">
                <div class="card__content">
                    <h3 class="card__title">Mental Wellness</h3>
                    <p class="card__excerpt">
                        Counseling services, mindfulness programs, and peer support resources.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Athletics CTA -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-section__title">Go Cardinal!</h2>
        <p class="cta-section__description">
            Cheer on our 36 varsity sports teams and experience the excitement of collegiate athletics.
        </p>
        <a href="#" class="btn btn-secondary btn-lg">Athletics Website</a>
    </div>
</section>

<?= $this->endSection() ?>

