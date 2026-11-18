import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('filterSheet', () => ({
        open: false,
        init() {
            const desktop = window.matchMedia('(min-width: 768px)');
            desktop.addEventListener('change', () => {
                if (desktop.matches) {
                    this.close();
                }
            });
        },
        show() {
            this.open = true;
            this.$refs.dialog.hidden = false;
            this.$refs.backdrop.hidden = false;
            document.body.classList.add('filters-open');
            this.$nextTick(() => {
                this.$refs.close?.focus();
                window.dispatchEvent(new Event('resize'));
            });
        },
        close() {
            if (! this.open) {
                return;
            }

            this.open = false;
            this.$refs.dialog.hidden = true;
            this.$refs.backdrop.hidden = true;
            document.body.classList.remove('filters-open');
            this.$refs.trigger?.focus();
        },
        trap(event) {
            if (! this.open || event.key !== 'Tab') {
                return;
            }

            const dialog = this.$refs.dialog;
            const focusable = [...dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])')]
                .filter((node) => node.getClientRects().length > 0);

            if (focusable.length === 0) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (! event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }));

    Alpine.data('motionIndicator', () => ({
        init() {
            const place = () => {
                const active = this.$root.querySelector('[data-active="1"]');
                const indicator = this.$refs.indicator;

                if (! active || ! indicator) {
                    return;
                }

                const key = `varza-indicator:${this.$root.dataset.indicator || 'nav'}`;
                let previous = null;

                try {
                    previous = sessionStorage.getItem(key);
                } catch (error) {
                    previous = null;
                }

                const x = `${active.offsetLeft}px`;
                const y = `${active.offsetTop}px`;
                const bar = this.$root.dataset.indicatorMode === 'bar';

                indicator.style.width = `${active.offsetWidth}px`;

                if (! bar) {
                    indicator.style.height = `${active.offsetHeight}px`;
                }

                const next = bar ? `translateX(${x})` : `translate(${x}, ${y})`;

                const stored = previous && previous !== `${x},${y}`;

                if (stored) {
                    const [px, py] = previous.split(',');
                    indicator.hidden = false;
                    indicator.style.transition = 'none';
                    indicator.style.transform = bar ? `translateX(${px})` : `translate(${px}, ${py})`;
                    void indicator.offsetWidth;
                    indicator.style.removeProperty('transition');
                    indicator.style.transform = next;
                } else {
                    indicator.style.transform = next;
                    indicator.hidden = false;
                }

                try {
                    sessionStorage.setItem(key, `${x},${y}`);
                } catch (error) {
                    // The indicator still sits on the active item.
                }
            };

            this.$nextTick(place);
            window.addEventListener('resize', place);
        },
    }));
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (! (form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post' || event.defaultPrevented) {
        return;
    }

    if (form.dataset.pending === '1') {
        event.preventDefault();

        return;
    }

    const submitter = event.submitter;
    let hidden = null;

    if (submitter instanceof HTMLButtonElement && submitter.name) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = submitter.name;
        hidden.value = submitter.value;
        form.append(hidden);
    }

    form.dataset.pending = '1';

    window.setTimeout(() => {
        if (event.defaultPrevented) {
            delete form.dataset.pending;
            hidden?.remove();

            return;
        }

        form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
            button.disabled = true;
        });

        if (submitter instanceof HTMLButtonElement) {
            submitter.textContent = '';
            const spin = document.createElement('span');
            spin.className = 'motion-spin';
            spin.setAttribute('aria-hidden', 'true');
            submitter.append(spin, document.createTextNode('Saving…'));
        }
    }, 0);
});

Alpine.start();
