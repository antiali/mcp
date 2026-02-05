/**
 * AI Site Builder Pro - Ultra Modern Micro-interactions
 *
 * Advanced animations, particles, and interactive effects
 *
 * @package AISiteBuilderPro
 * @since 1.2.0
 */

(function($) {
    'use strict';

    // Ultra Modern Effects Controller
    const AISBPUltraEffects = {
        
        /**
         * Initialize all effects
         */
        init: function() {
            this.initParticles();
            this.initRippleEffect();
            // this.initMagneticButtons();
            this.initSmoothScrollReveal();
            this.initTypingEffect();
            this.initCounterAnimation();
            // this.initTiltEffect();
            // this.initCursorEffects();
            console.log('🎨 Ultra Modern Effects initialized');
        },

        /**
         * Particle Background System
         */
        initParticles: function() {
            const canvas = document.createElement('canvas');
            canvas.id = 'aisbp-particles';
            canvas.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 0;
                opacity: 0.5;
            `;
            
            const container = document.querySelector('.aisbp-wizard-container, .aisbp-page-container');
            if (container) {
                container.style.position = 'relative';
                container.insertBefore(canvas, container.firstChild);
                this.animateParticles(canvas);
            }
        },

        animateParticles: function(canvas) {
            const ctx = canvas.getContext('2d');
            const particles = [];
            const particleCount = 50;

            const resize = () => {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            };
            resize();
            window.addEventListener('resize', resize);

            class Particle {
                constructor() {
                    this.reset();
                }

                reset() {
                    this.x = Math.random() * canvas.width;
                    this.y = Math.random() * canvas.height;
                    this.size = Math.random() * 2 + 1;
                    this.speedX = Math.random() * 0.5 - 0.25;
                    this.speedY = Math.random() * 0.5 - 0.25;
                    this.opacity = Math.random() * 0.5 + 0.2;
                    this.color = `hsla(${Math.random() * 60 + 220}, 80%, 60%, ${this.opacity})`;
                }

                update() {
                    this.x += this.speedX;
                    this.y += this.speedY;

                    if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
                    if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
                }

                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                    ctx.fillStyle = this.color;
                    ctx.fill();
                }
            }

            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }

            const animate = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                particles.forEach((particle, i) => {
                    particle.update();
                    particle.draw();

                    // Connect nearby particles
                    particles.slice(i + 1).forEach(other => {
                        const dx = particle.x - other.x;
                        const dy = particle.y - other.y;
                        const distance = Math.sqrt(dx * dx + dy * dy);

                        if (distance < 150) {
                            ctx.beginPath();
                            ctx.strokeStyle = `rgba(99, 102, 241, ${0.1 * (1 - distance / 150)})`;
                            ctx.lineWidth = 0.5;
                            ctx.moveTo(particle.x, particle.y);
                            ctx.lineTo(other.x, other.y);
                            ctx.stroke();
                        }
                    });
                });

                requestAnimationFrame(animate);
            };
            animate();
        },

        /**
         * Ripple Effect on Click
         */
        initRippleEffect: function() {
            $(document).on('click', '.aisbp-btn, .aisbp-card, .aisbp-model-card', function(e) {
                const $el = $(this);
                const ripple = $('<span class="aisbp-ripple"></span>');
                
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.css({
                    width: size + 'px',
                    height: size + 'px',
                    left: x + 'px',
                    top: y + 'px'
                });
                
                $el.css('overflow', 'hidden').append(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });

            // Add ripple styles
            if (!$('#aisbp-ripple-styles').length) {
                $('head').append(`
                    <style id="aisbp-ripple-styles">
                        .aisbp-ripple {
                            position: absolute;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.3);
                            transform: scale(0);
                            animation: aisbpRipple 0.6s linear;
                            pointer-events: none;
                        }
                        @keyframes aisbpRipple {
                            to {
                                transform: scale(4);
                                opacity: 0;
                            }
                        }
                    </style>
                `);
            }
        },

        /**
         * Magnetic Button Effect - Disabled
         */
        initMagneticButtons: function() {
            // Disabled
        },

        /**
         * Smooth Scroll Reveal Animation
         */
        initSmoothScrollReveal: function() {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('aisbp-revealed');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.aisbp-card, .aisbp-model-card, .aisbp-type-card').forEach(el => {
                el.classList.add('aisbp-reveal-element');
                observer.observe(el);
            });

            // Add reveal styles
            if (!$('#aisbp-reveal-styles').length) {
                $('head').append(`
                    <style id="aisbp-reveal-styles">
                        .aisbp-reveal-element {
                            opacity: 0;
                            transform: translateY(30px);
                            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
                        }
                        .aisbp-reveal-element.aisbp-revealed {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    </style>
                `);
            }
        },

        /**
         * Typing Effect for Headings
         */
        initTypingEffect: function() {
            const typingElements = document.querySelectorAll('[data-typing]');
            
            typingElements.forEach(el => {
                const text = el.textContent;
                el.textContent = '';
                el.style.borderRight = '2px solid var(--aisbp-neon-blue)';
                
                let i = 0;
                const type = () => {
                    if (i < text.length) {
                        el.textContent += text.charAt(i);
                        i++;
                        setTimeout(type, 50);
                    } else {
                        el.style.borderRight = 'none';
                    }
                };
                type();
            });
        },

        /**
         * Counter Animation for Numbers
         */
        initCounterAnimation: function() {
            const counters = document.querySelectorAll('[data-counter]');
            
            const animateCounter = (el) => {
                const target = parseInt(el.getAttribute('data-counter'));
                const duration = 2000;
                const start = 0;
                const startTime = performance.now();
                
                const update = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const current = Math.floor(start + (target - start) * eased);
                    
                    el.textContent = current.toLocaleString();
                    
                    if (progress < 1) {
                        requestAnimationFrame(update);
                    }
                };
                
                requestAnimationFrame(update);
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            counters.forEach(counter => observer.observe(counter));
        },

        /**
         * 3D Tilt Effect for Cards - Disabled to prevent mouse following
         */
        initTiltEffect: function() {
            // Disabled
        },

        /**
         * Custom Cursor Effects - Disabled
         */
        initCursorEffects: function() {
            // Disabled
        },

        /**
         * Confetti Celebration
         */
        showConfetti: function() {
            const container = document.createElement('div');
            container.className = 'aisbp-confetti';
            document.body.appendChild(container);

            const colors = ['#667eea', '#764ba2', '#00d4ff', '#00ff87', '#ff00c8'];
            
            for (let i = 0; i < 100; i++) {
                const piece = document.createElement('div');
                piece.className = 'aisbp-confetti-piece';
                piece.style.left = Math.random() * 100 + '%';
                piece.style.background = colors[Math.floor(Math.random() * colors.length)];
                piece.style.animationDelay = Math.random() * 2 + 's';
                piece.style.animationDuration = Math.random() * 2 + 2 + 's';
                container.appendChild(piece);
            }

            setTimeout(() => container.remove(), 5000);
        },

        /**
         * Show Ultra Toast Notification
         */
        showToast: function(title, message, type = 'success') {
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };

            const toast = $(`
                <div class="aisbp-toast-ultra ${type}">
                    <div class="aisbp-toast-icon">${icons[type]}</div>
                    <div class="aisbp-toast-content">
                        <h4>${title}</h4>
                        <p>${message}</p>
                    </div>
                </div>
            `);

            $('body').append(toast);
            
            setTimeout(() => toast.addClass('show'), 100);
            setTimeout(() => {
                toast.removeClass('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        },

        /**
         * AI Generation Loader
         */
        showAILoader: function(container, message = 'جاري الإنشاء...') {
            const loader = `
                <div class="aisbp-ai-generating">
                    <div class="aisbp-ai-orb">
                        <div class="aisbp-ai-orb-rings">
                            <div class="aisbp-ai-orb-ring"></div>
                            <div class="aisbp-ai-orb-ring"></div>
                            <div class="aisbp-ai-orb-ring"></div>
                        </div>
                    </div>
                    <div class="aisbp-ai-status">${message}</div>
                    <div class="aisbp-ai-substatus">
                        <span class="aisbp-typing-dots">
                            <span></span><span></span><span></span>
                        </span>
                    </div>
                </div>
            `;
            
            $(container).html(loader);
        }
    };

    // Initialize on DOM ready
    $(document).ready(function() {
        AISBPUltraEffects.init();
    });

    // Expose to global
    window.AISBPUltraEffects = AISBPUltraEffects;

})(jQuery);
