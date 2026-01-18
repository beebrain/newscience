<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">Research</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <span>Research</span>
        </div>
    </div>
</section>

<!-- Intro Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">Research Excellence</span>
            <h2 class="section-header__title">Driving Innovation & Discovery</h2>
            <p class="section-header__description">
                Our researchers are tackling the world's most pressing challenges, from climate 
                change to healthcare, from technology to social justice.
            </p>
        </div>
    </div>
</section>

<!-- Research Areas -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">Areas of Focus</span>
            <h2 class="section-header__title">Research That Matters</h2>
        </div>
        
        <div class="grid grid-3">
            <div class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=400&h=200&fit=crop" alt="AI Research" class="card__image">
                <div class="card__content">
                    <span class="card__category">Technology</span>
                    <h3 class="card__title">Artificial Intelligence</h3>
                    <p class="card__excerpt">
                        Pioneering advances in machine learning, robotics, and computational systems.
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1576086213369-97a306d36557?w=400&h=200&fit=crop" alt="Healthcare Research" class="card__image">
                <div class="card__content">
                    <span class="card__category">Health</span>
                    <h3 class="card__title">Biomedical Sciences</h3>
                    <p class="card__excerpt">
                        Developing new treatments and understanding the mechanisms of disease.
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=400&h=200&fit=crop" alt="Climate Research" class="card__image">
                <div class="card__content">
                    <span class="card__category">Environment</span>
                    <h3 class="card__title">Climate & Sustainability</h3>
                    <p class="card__excerpt">
                        Addressing environmental challenges and building a sustainable future.
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=200&fit=crop" alt="Social Sciences" class="card__image">
                <div class="card__content">
                    <span class="card__category">Society</span>
                    <h3 class="card__title">Social Sciences</h3>
                    <p class="card__excerpt">
                        Understanding human behavior, economics, and social structures.
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1462331940025-496dfbfc7564?w=400&h=200&fit=crop" alt="Space Research" class="card__image">
                <div class="card__content">
                    <span class="card__category">Space</span>
                    <h3 class="card__title">Space Exploration</h3>
                    <p class="card__excerpt">
                        Exploring the cosmos and developing technologies for space travel.
                    </p>
                </div>
            </div>
            
            <div class="card animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?w=400&h=200&fit=crop" alt="Engineering" class="card__image">
                <div class="card__content">
                    <span class="card__category">Engineering</span>
                    <h3 class="card__title">Advanced Engineering</h3>
                    <p class="card__excerpt">
                        Creating breakthrough technologies that transform industries.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section section-primary">
    <div class="container">
        <div class="stats">
            <div class="stat animate-on-scroll">
                <div class="stat__number">$1.8B</div>
                <div class="stat__label">Annual Research Funding</div>
            </div>
            <div class="stat animate-on-scroll">
                <div class="stat__number">5,000+</div>
                <div class="stat__label">Active Research Projects</div>
            </div>
            <div class="stat animate-on-scroll">
                <div class="stat__number">18</div>
                <div class="stat__label">Research Institutes</div>
            </div>
            <div class="stat animate-on-scroll">
                <div class="stat__number">1,000+</div>
                <div class="stat__label">Industry Partners</div>
            </div>
        </div>
    </div>
</section>

<!-- Research Centers -->
<section class="section">
    <div class="container">
        <div class="feature-section">
            <div class="feature-section__image animate-on-scroll">
                <img src="https://images.unsplash.com/photo-1567427017947-545c5f8d16ad?w=600&h=400&fit=crop" alt="Research Center">
            </div>
            <div class="feature-section__content animate-on-scroll">
                <span class="feature-section__subtitle">Infrastructure</span>
                <h2 class="feature-section__title">World-Class Research Facilities</h2>
                <p class="feature-section__description">
                    Our state-of-the-art research centers provide the infrastructure and 
                    resources needed to conduct groundbreaking research across all disciplines.
                </p>
                <ul class="feature-list">
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>Advanced Computing Clusters</span>
                    </li>
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>Specialized Laboratories</span>
                    </li>
                    <li class="feature-list__item">
                        <span class="feature-list__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>Collaborative Workspaces</span>
                    </li>
                </ul>
                <a href="#" class="btn btn-primary">Explore Centers</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-section__title">Partner With Us</h2>
        <p class="cta-section__description">
            Collaborate with our researchers to solve real-world problems and drive innovation.
        </p>
        <a href="<?= base_url('contact') ?>" class="btn btn-secondary btn-lg">Get in Touch</a>
    </div>
</section>

<?= $this->endSection() ?>
