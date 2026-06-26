document.addEventListener('DOMContentLoaded', () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const navToggle = document.getElementById('nav-toggle');
    const mobileMenu = document.getElementById('mobile-menu');

    navToggle?.addEventListener('click', () => {
        const isOpen = mobileMenu.classList.toggle('menu-open');
        navToggle.classList.toggle('menu-open', isOpen);
        navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    mobileMenu?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('menu-open');
            navToggle?.classList.remove('menu-open');
            navToggle?.setAttribute('aria-expanded', 'false');
        });
    });

    const revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const siblings = Array.from(entry.target.parentElement?.children ?? []).filter((el) =>
                        el.classList.contains('reveal')
                    );
                    const index = Math.max(siblings.indexOf(entry.target), 0);
                    entry.target.style.transitionDelay = `${Math.min(index, 5) * 90}ms`;
                    entry.target.classList.add('is-visible');
                }
            });
        },
        { threshold: 0.15 }
    );

    document.querySelectorAll('.reveal').forEach((el) => revealObserver.observe(el));

    const navLinks = document.querySelectorAll('.nav-link');
    const navObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    navLinks.forEach((link) => {
                        const isActive = link.dataset.section === entry.target.id;
                        link.classList.toggle('text-white', isActive);
                        link.classList.toggle('text-slate-400', !isActive);
                        link.classList.toggle('is-active', isActive);
                    });
                }
            });
        },
        { rootMargin: '-40% 0px -55% 0px' }
    );

    document.querySelectorAll('section[id]').forEach((section) => navObserver.observe(section));

    const backToTop = document.getElementById('back-to-top');
    const header = document.querySelector('header');
    const heroCard = document.querySelector('#hero .glass-panel');
    const scrollProgressBar = document.getElementById('scroll-progress');

    let ticking = false;
    const updateScrollEffects = () => {
        const scrollY = window.scrollY;

        const isPastTop = scrollY > 400;
        backToTop?.classList.toggle('opacity-0', !isPastTop);
        backToTop?.classList.toggle('pointer-events-none', !isPastTop);

        header?.classList.toggle('header-scrolled', scrollY > 40);

        if (heroCard && !prefersReducedMotion) {
            const fade = Math.min(scrollY / 600, 1);
            heroCard.style.transform = `translateY(${scrollY * 0.15}px)`;
            heroCard.style.opacity = `${1 - fade}`;
        }

        if (scrollProgressBar) {
            const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
            const progress = maxScroll > 0 ? Math.min(Math.max(scrollY / maxScroll, 0), 1) : 0;
            scrollProgressBar.style.transform = `scaleX(${progress})`;
        }
    };

    window.addEventListener(
        'scroll',
        () => {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(() => {
                    updateScrollEffects();
                    ticking = false;
                });
            }
        },
        { passive: true }
    );
    updateScrollEffects();

    backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    const copyEmailBtn = document.getElementById('copy-email');
    copyEmailBtn?.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText('mr.michaelrenon@gmail.com');
            const original = copyEmailBtn.textContent;
            copyEmailBtn.textContent = 'Copied!';
            setTimeout(() => {
                copyEmailBtn.textContent = original;
            }, 1800);
        } catch {
            window.location.href = 'mailto:mr.michaelrenon@gmail.com';
        }
    });

    const projectsViewMore = document.getElementById('projects-view-more');
    const extraProjects = document.querySelectorAll('.project-extra');
    if (projectsViewMore && extraProjects.length) {
        const label = projectsViewMore.querySelector('span');
        const chevron = projectsViewMore.querySelector('svg');

        projectsViewMore.addEventListener('click', () => {
            const expanding = projectsViewMore.dataset.expanded !== 'true';

            extraProjects.forEach((el, index) => {
                if (expanding) {
                    el.style.transitionDelay = `${index * 90}ms`;
                    el.classList.remove('hidden');
                    requestAnimationFrame(() => el.classList.add('is-visible'));
                } else {
                    el.style.transitionDelay = '0ms';
                    el.classList.remove('is-visible');
                    setTimeout(() => el.classList.add('hidden'), 600);
                }
            });

            projectsViewMore.dataset.expanded = expanding ? 'true' : 'false';
            label.textContent = expanding ? 'View Less' : 'View More';
            chevron.classList.toggle('rotate-180', expanding);
        });
    }

    const yearEl = document.getElementById('year');
    if (yearEl) {
        yearEl.textContent = new Date().getFullYear();
    }
});
