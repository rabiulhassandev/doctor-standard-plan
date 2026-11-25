const menuToggle = document.querySelector('[data-menu-toggle]');
const mobileNavigation = document.getElementById('mobile-navigation');

if (menuToggle && mobileNavigation) {
    const closeMenu = () => {
        menuToggle.setAttribute('aria-expanded', 'false');
        mobileNavigation.hidden = true;
    };

    menuToggle.addEventListener('click', () => {
        const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
        menuToggle.setAttribute('aria-expanded', String(!isExpanded));
        mobileNavigation.hidden = isExpanded;
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !mobileNavigation.hidden) {
            closeMenu();
            menuToggle.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!mobileNavigation.hidden && !mobileNavigation.contains(event.target) && !menuToggle.contains(event.target)) {
            closeMenu();
        }
    });
}

document.querySelectorAll('[data-video-load]').forEach((button) => {
    button.addEventListener('click', () => {
        const player = button.closest('[data-video-player]');
        const videoId = player?.dataset.videoId;

        if (!player || !videoId || !/^[A-Za-z0-9_-]{11}$/.test(videoId)) {
            return;
        }

        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube-nocookie.com/embed/${videoId}?autoplay=1&rel=0`;
        iframe.title = player.dataset.videoTitle || 'YouTube video';
        iframe.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen';
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        iframe.allowFullscreen = true;
        player.replaceChildren(iframe);
        iframe.focus();
    });
});

document.querySelectorAll('[data-public-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        if (form.checkValidity()) {
            const button = form.querySelector('[type="submit"]');
            if (button) {
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
            }
        }
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-public-form] [type="submit"]').forEach((button) => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    });
});

const analyticsId = document.body.dataset.analyticsId;
const consentBanner = document.querySelector('[data-consent-banner]');
const consentKey = 'practice-analytics-consent';

if (analyticsId && /^G-[A-Z0-9]+$/.test(analyticsId) && consentBanner) {
    let analyticsLoaded = false;
    let storedConsent = null;

    try {
        storedConsent = localStorage.getItem(consentKey);
    } catch {
        storedConsent = null;
    }

    const loadAnalytics = () => {
        if (analyticsLoaded) {
            return;
        }

        analyticsLoaded = true;
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () {
            window.dataLayer.push(arguments);
        };
        window.gtag('js', new Date());
        window.gtag('config', analyticsId, {
            page_location: `${window.location.origin}${window.location.pathname}`,
            page_referrer: '',
            allow_google_signals: false,
            allow_ad_personalization_signals: false,
        });
        const script = document.createElement('script');
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtag/js?id=${analyticsId}`;
        document.head.appendChild(script);
    };

    consentBanner.hidden = storedConsent === 'granted' || storedConsent === 'denied';
    if (storedConsent === 'granted') {
        loadAnalytics();
    }

    document.querySelectorAll('[data-consent]').forEach((button) => {
        button.addEventListener('click', () => {
            const consent = button.dataset.consent;
            try {
                localStorage.setItem(consentKey, consent);
            } catch {
                storedConsent = consent;
            }
            consentBanner.hidden = true;
            if (consent === 'granted') {
                loadAnalytics();
            } else if (analyticsLoaded) {
                window[`ga-disable-${analyticsId}`] = true;
                document.cookie.split(';').forEach((cookie) => {
                    const name = cookie.split('=')[0].trim();
                    if (name === '_ga' || name.startsWith('_ga_')) {
                        const suffixes = window.location.hostname.split('.');
                        document.cookie = `${name}=; Max-Age=0; path=/`;
                        for (let index = 0; index < suffixes.length - 1; index++) {
                            document.cookie = `${name}=; Max-Age=0; path=/; domain=.${suffixes.slice(index).join('.')}`;
                        }
                    }
                });
                window.location.reload();
            }
        });
    });

    document.querySelectorAll('[data-privacy-open]').forEach((button) => {
        button.addEventListener('click', () => {
            consentBanner.hidden = false;
            consentBanner.querySelector('[data-consent]')?.focus();
        });
    });
}

const homeNotice = document.querySelector('[data-home-notice]');
const noticeMarquee = homeNotice?.querySelector('[data-notice-marquee]');
const noticeToggle = homeNotice?.querySelector('[data-notice-toggle]');

if (noticeToggle && typeof noticeMarquee?.start === 'function' && typeof noticeMarquee?.stop === 'function') {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let paused = false;

    const updateNotice = () => {
        const scrolling = !paused && !reducedMotion.matches;
        homeNotice.toggleAttribute('data-scrolling', scrolling);
        noticeToggle.hidden = reducedMotion.matches;
        noticeToggle.setAttribute('aria-label', paused ? noticeToggle.dataset.resumeLabel : noticeToggle.dataset.pauseLabel);
        noticeMarquee.hidden = !scrolling;

        if (scrolling) {
            noticeMarquee.start();
        } else {
            noticeMarquee.stop();
        }
    };

    noticeToggle.addEventListener('click', () => {
        paused = !paused;
        updateNotice();
    });
    reducedMotion.addEventListener('change', updateNotice);
    updateNotice();
}

document.querySelectorAll('[data-photo-slider]').forEach((slider) => {
    const photos = [...slider.querySelectorAll('[data-photo-slide]')];
    const dots = [...slider.querySelectorAll('[data-photo-select]')];
    const videos = [...slider.querySelectorAll('[data-slide-video]')];
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const pausedVideos = new Set();

    const syncVideos = () => {
        videos.forEach((video) => {
            if (video.closest('[data-photo-slide]').hidden || document.hidden || reducedMotion.matches || pausedVideos.has(video)) {
                video.pause();
            } else {
                video.play().catch(() => {});
            }
        });
    };

    videos.forEach((video) => {
        const videoToggle = video.closest('[data-photo-slide]').querySelector('[data-slide-video-toggle]');
        const updateVideoControl = () => {
            videoToggle.setAttribute('aria-label', video.paused ? videoToggle.dataset.playLabel : videoToggle.dataset.pauseLabel);
            videoToggle.querySelector('[data-video-play-icon]').hidden = !video.paused;
            videoToggle.querySelector('[data-video-pause-icon]').hidden = video.paused;
        };
        video.muted = true;
        video.addEventListener('play', updateVideoControl);
        video.addEventListener('pause', updateVideoControl);
        videoToggle.addEventListener('click', () => {
            if (video.paused) {
                pausedVideos.delete(video);
                video.play().catch(() => {});
            } else {
                pausedVideos.add(video);
                video.pause();
            }
        });
    });
    if (videos.length) {
        document.addEventListener('visibilitychange', syncVideos);
        reducedMotion.addEventListener('change', syncVideos);
        syncVideos();
    }

    if (photos.length < 2) return;

    const rotationToggle = slider.querySelector('[data-photo-rotation]');
    const rotationInterval = Number(slider.dataset.photoInterval);
    let selected = 0;
    let rotationTimer;
    let rotationPaused = false;

    const scheduleRotation = () => {
        window.clearTimeout(rotationTimer);
        if (!rotationToggle || rotationPaused || document.hidden || reducedMotion.matches) return;

        rotationTimer = window.setTimeout(() => {
            if (photos[selected].contains(document.activeElement)) {
                scheduleRotation();
                return;
            }
            show(selected + 1);
        }, rotationInterval);
    };

    const show = (index) => {
        selected = (index + photos.length) % photos.length;
        photos.forEach((photo, position) => {
            photo.hidden = position !== selected;
            photo.inert = photo.hidden;
        });
        dots.forEach((dot, position) => dot.setAttribute('aria-current', String(position === selected)));
        syncVideos();
        scheduleRotation();
    };

    if (rotationToggle) {
        const updateRotation = () => {
            rotationToggle.hidden = reducedMotion.matches;
            rotationToggle.setAttribute('aria-label', rotationPaused ? rotationToggle.dataset.playLabel : rotationToggle.dataset.pauseLabel);
            rotationToggle.querySelector('[data-video-play-icon]').hidden = !rotationPaused;
            rotationToggle.querySelector('[data-video-pause-icon]').hidden = rotationPaused;
            scheduleRotation();
        };

        rotationToggle.addEventListener('click', () => {
            rotationPaused = !rotationPaused;
            updateRotation();
        });
        document.addEventListener('visibilitychange', scheduleRotation);
        reducedMotion.addEventListener('change', updateRotation);
        updateRotation();
    }

    slider.querySelector('[data-photo-prev]').addEventListener('click', () => show(selected - 1));
    slider.querySelector('[data-photo-next]').addEventListener('click', () => show(selected + 1));
    dots.forEach((dot, index) => dot.addEventListener('click', () => show(index)));
    slider.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            show(selected + (event.key === 'ArrowRight' ? 1 : -1));
        }
    });
});
