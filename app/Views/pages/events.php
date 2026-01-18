<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">Events</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <span>Events</span>
        </div>
    </div>
</section>

<!-- Events Calendar -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-header__subtitle">What's Happening</span>
            <h2 class="section-header__title">Upcoming Events</h2>
            <p class="section-header__description">
                Discover lectures, performances, workshops, and more happening across campus.
            </p>
        </div>
        
        <div class="grid grid-2">
            <!-- Event Card with Date -->
            <article class="card animate-on-scroll" style="display: flex; flex-direction: row;">
                <div style="background: var(--color-primary); padding: var(--spacing-6); display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 100px;">
                    <span style="font-size: var(--text-3xl); font-weight: 700; color: var(--color-dark);">25</span>
                    <span style="font-size: var(--text-sm); font-weight: 600; color: var(--color-dark); text-transform: uppercase;">Jan</span>
                </div>
                <div class="card__content" style="flex: 1;">
                    <span class="card__category">Lecture</span>
                    <h3 class="card__title">
                        <a href="#">The Future of Sustainable Energy</a>
                    </h3>
                    <p class="card__excerpt">
                        Join Dr. Emma Richardson as she explores innovations in renewable energy technology.
                    </p>
                    <div class="card__meta">
                        <span>4:00 PM</span>
                        <span>•</span>
                        <span>Main Auditorium</span>
                    </div>
                </div>
            </article>
            
            <article class="card animate-on-scroll" style="display: flex; flex-direction: row;">
                <div style="background: var(--color-primary); padding: var(--spacing-6); display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 100px;">
                    <span style="font-size: var(--text-3xl); font-weight: 700; color: var(--color-dark);">01</span>
                    <span style="font-size: var(--text-sm); font-weight: 600; color: var(--color-dark); text-transform: uppercase;">Feb</span>
                </div>
                <div class="card__content" style="flex: 1;">
                    <span class="card__category">Concert</span>
                    <h3 class="card__title">
                        <a href="#">Spring Symphony Orchestra Performance</a>
                    </h3>
                    <p class="card__excerpt">
                        An evening of classical masterpieces performed by our award-winning orchestra.
                    </p>
                    <div class="card__meta">
                        <span>7:30 PM</span>
                        <span>•</span>
                        <span>Concert Hall</span>
                    </div>
                </div>
            </article>
            
            <article class="card animate-on-scroll" style="display: flex; flex-direction: row;">
                <div style="background: var(--color-primary); padding: var(--spacing-6); display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 100px;">
                    <span style="font-size: var(--text-3xl); font-weight: 700; color: var(--color-dark);">10</span>
                    <span style="font-size: var(--text-sm); font-weight: 600; color: var(--color-dark); text-transform: uppercase;">Feb</span>
                </div>
                <div class="card__content" style="flex: 1;">
                    <span class="card__category">Workshop</span>
                    <h3 class="card__title">
                        <a href="#">AI & Machine Learning Bootcamp</a>
                    </h3>
                    <p class="card__excerpt">
                        Three-day intensive workshop on the fundamentals of artificial intelligence.
                    </p>
                    <div class="card__meta">
                        <span>All Day</span>
                        <span>•</span>
                        <span>Engineering Building</span>
                    </div>
                </div>
            </article>
            
            <article class="card animate-on-scroll" style="display: flex; flex-direction: row;">
                <div style="background: var(--color-primary); padding: var(--spacing-6); display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 100px;">
                    <span style="font-size: var(--text-3xl); font-weight: 700; color: var(--color-dark);">14</span>
                    <span style="font-size: var(--text-sm); font-weight: 600; color: var(--color-dark); text-transform: uppercase;">Feb</span>
                </div>
                <div class="card__content" style="flex: 1;">
                    <span class="card__category">Exhibit</span>
                    <h3 class="card__title">
                        <a href="#">Modern Art Exhibition Opening</a>
                    </h3>
                    <p class="card__excerpt">
                        Featuring works by emerging artists exploring themes of identity and technology.
                    </p>
                    <div class="card__meta">
                        <span>6:00 PM</span>
                        <span>•</span>
                        <span>Art Gallery</span>
                    </div>
                </div>
            </article>
            
            <article class="card animate-on-scroll" style="display: flex; flex-direction: row;">
                <div style="background: var(--color-primary); padding: var(--spacing-6); display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 100px;">
                    <span style="font-size: var(--text-3xl); font-weight: 700; color: var(--color-dark);">20</span>
                    <span style="font-size: var(--text-sm); font-weight: 600; color: var(--color-dark); text-transform: uppercase;">Feb</span>
                </div>
                <div class="card__content" style="flex: 1;">
                    <span class="card__category">Conference</span>
                    <h3 class="card__title">
                        <a href="#">Global Health Summit 2026</a>
                    </h3>
                    <p class="card__excerpt">
                        International experts discuss public health challenges and innovations.
                    </p>
                    <div class="card__meta">
                        <span>9:00 AM</span>
                        <span>•</span>
                        <span>Medical Center</span>
                    </div>
                </div>
            </article>
            
            <article class="card animate-on-scroll" style="display: flex; flex-direction: row;">
                <div style="background: var(--color-primary); padding: var(--spacing-6); display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 100px;">
                    <span style="font-size: var(--text-3xl); font-weight: 700; color: var(--color-dark);">28</span>
                    <span style="font-size: var(--text-sm); font-weight: 600; color: var(--color-dark); text-transform: uppercase;">Feb</span>
                </div>
                <div class="card__content" style="flex: 1;">
                    <span class="card__category">Sports</span>
                    <h3 class="card__title">
                        <a href="#">Homecoming Basketball Game</a>
                    </h3>
                    <p class="card__excerpt">
                        Cheer on our team as they take on the conference rivals.
                    </p>
                    <div class="card__meta">
                        <span>7:00 PM</span>
                        <span>•</span>
                        <span>Main Arena</span>
                    </div>
                </div>
            </article>
        </div>
        
        <!-- Load More -->
        <div class="text-center mt-8">
            <a href="#" class="btn btn-outline">View Full Calendar</a>
        </div>
    </div>
</section>

<!-- Submit Event CTA -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-section__title">Have an Event?</h2>
        <p class="cta-section__description">
            Submit your campus event to be featured on our events calendar.
        </p>
        <a href="#" class="btn btn-secondary btn-lg">Submit an Event</a>
    </div>
</section>

<?= $this->endSection() ?>
