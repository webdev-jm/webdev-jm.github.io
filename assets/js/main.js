document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.getElementById('nav-toggle');
    const mobileMenu = document.getElementById('mobile-menu');

    navToggle?.addEventListener('click', () => {
        const isHidden = mobileMenu.classList.toggle('hidden');
        navToggle.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
    });

    mobileMenu?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => mobileMenu.classList.add('hidden'));
    });

    const revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
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
                    });
                }
            });
        },
        { rootMargin: '-40% 0px -55% 0px' }
    );

    document.querySelectorAll('section[id]').forEach((section) => navObserver.observe(section));

    const backToTop = document.getElementById('back-to-top');
    window.addEventListener('scroll', () => {
        const isVisible = window.scrollY > 400;
        backToTop?.classList.toggle('opacity-0', !isVisible);
        backToTop?.classList.toggle('pointer-events-none', !isVisible);
    });
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

            extraProjects.forEach((el) => {
                if (expanding) {
                    el.classList.remove('hidden');
                    requestAnimationFrame(() => el.classList.add('is-visible'));
                } else {
                    el.classList.add('hidden');
                    el.classList.remove('is-visible');
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
