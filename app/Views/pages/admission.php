<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">Admission</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <span>Admission</span>
        </div>
    </div>
</section>

<!-- Intro Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">Join Our Community</span>
            <h2 class="section-header__title">Begin Your Journey</h2>
            <p class="section-header__description">
                Explore the possibilities of an education at our university. We look for distinctive 
                students who exhibit an abundance of energy and curiosity.
            </p>
        </div>
    </div>
</section>

<!-- Admission Types -->
<section class="section section-light">
    <div class="container">
        <div class="grid grid-2">
            <div class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=600&h=300&fit=crop" alt="Undergraduate Students" class="card__image">
                <div class="card__content">
                    <span class="card__category">Undergraduate</span>
                    <h3 class="card__title">Undergraduate Admission</h3>
                    <p class="card__excerpt">
                        Join a diverse community of learners and discover your passion through 
                        our world-class undergraduate programs. We offer over 90 majors across 
                        seven schools.
                    </p>
                    <ul class="feature-list" style="margin-top: var(--spacing-4);">
                        <li class="feature-list__item">
                            <span class="feature-list__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                            <span>Application Deadline: January 2</span>
                        </li>
                        <li class="feature-list__item">
                            <span class="feature-list__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                            <span>Early Decision Available</span>
                        </li>
                    </ul>
                    <a href="#" class="btn btn-primary" style="margin-top: var(--spacing-4);">Apply Now</a>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=600&h=300&fit=crop" alt="Graduate Students" class="card__image">
                <div class="card__content">
                    <span class="card__category">Graduate</span>
                    <h3 class="card__title">Graduate Admission</h3>
                    <p class="card__excerpt">
                        Advance your career and expertise through our rigorous graduate programs. 
                        Join a community of scholars and researchers pushing the boundaries of knowledge.
                    </p>
                    <ul class="feature-list" style="margin-top: var(--spacing-4);">
                        <li class="feature-list__item">
                            <span class="feature-list__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                            <span>200+ Graduate Programs</span>
                        </li>
                        <li class="feature-list__item">
                            <span class="feature-list__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                            <span>Rolling Admissions</span>
                        </li>
                    </ul>
                    <a href="#" class="btn btn-primary" style="margin-top: var(--spacing-4);">Explore Programs</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Financial Aid -->
<section class="section">
    <div class="container">
        <div class="feature-section">
            <div class="feature-section__image animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=600&h=400&fit=crop" alt="Financial Aid">
            </div>
            <div class="feature-section__content animate-on-scroll">
                <span class="feature-section__subtitle">Financial Aid</span>
                <h2 class="feature-section__title">Making Education Accessible</h2>
                <p class="feature-section__description">
                    We meet the full financial need of every admitted undergraduate who qualifies 
                    for assistance. More than two-thirds of undergrads receive some form of 
                    financial assistance.
                </p>
                <div class="card" style="margin-bottom: var(--spacing-6); background: var(--color-primary-light);">
                    <div class="card__content">
                        <p style="margin: 0; font-weight: 600;">
                            Generally, tuition is covered for families with incomes below $150,000.
                        </p>
                    </div>
                </div>
                <a href="#" class="btn btn-primary">Calculate Your Aid</a>
            </div>
        </div>
    </div>
</section>

<!-- Application Steps -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">How to Apply</span>
            <h2 class="section-header__title">Application Process</h2>
        </div>
        
        <div class="grid grid-4">
            <div class="card animate-on-scroll">
                <div class="card__content text-center">
                    <div class="stat__number" style="color: var(--color-primary-dark);">1</div>
                    <h4 class="card__title">Research</h4>
                    <p class="card__excerpt">
                        Explore our programs and find the right fit for your academic goals.
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <div class="card__content text-center">
                    <div class="stat__number" style="color: var(--color-primary-dark);">2</div>
                    <h4 class="card__title">Prepare</h4>
                    <p class="card__excerpt">
                        Gather your transcripts, test scores, and recommendation letters.
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <div class="card__content text-center">
                    <div class="stat__number" style="color: var(--color-primary-dark);">3</div>
                    <h4 class="card__title">Apply</h4>
                    <p class="card__excerpt">
                        Submit your application through our online portal before the deadline.
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <div class="card__content text-center">
                    <div class="stat__number" style="color: var(--color-primary-dark);">4</div>
                    <h4 class="card__title">Enroll</h4>
                    <p class="card__excerpt">
                        Accept your offer and join our community of scholars and innovators.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Visit Campus -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-section__title">Visit Our Campus</h2>
        <p class="cta-section__description">
            Experience our vibrant campus community firsthand. Schedule a tour or attend an information session.
        </p>
        <div class="flex justify-center gap-4">
            <a href="#" class="btn btn-secondary btn-lg">Schedule a Visit</a>
            <a href="#" class="btn btn-outline btn-lg">Virtual Tour</a>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

