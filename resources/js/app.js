import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
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

                if (previous) {
                    const [px, py] = previous.split(',');
                    indicator.style.transform = bar ? `translateX(${px})` : `translate(${px}, ${py})`;
                    requestAnimationFrame(() => {
                        indicator.style.transform = next;
                    });
                } else {
                    indicator.style.transform = next;
                }

                indicator.hidden = false;

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

    if (! (form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post') {
        return;
    }

    if (form.dataset.pending === '1') {
        event.preventDefault();

        return;
    }

    const submitter = event.submitter;

    if (submitter instanceof HTMLButtonElement && submitter.name) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = submitter.name;
        hidden.value = submitter.value;
        form.append(hidden);
    }

    form.dataset.pending = '1';

    window.setTimeout(() => {
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
