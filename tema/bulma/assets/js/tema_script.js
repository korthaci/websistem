document.querySelectorAll('.dropdown').forEach(dropdown => {
    dropdown.addEventListener('click', (e) => {
        e.stopPropagation();

        document.querySelectorAll('.dropdown.is-active').forEach(d => {
            if (d !== dropdown) {
                d.classList.remove('is-active');

                const m = d.querySelector('.dropdown-menu');
                if (m) m.style.top = '';
            }
        });

        dropdown.classList.toggle('is-active');

        const menu = dropdown.querySelector('.dropdown-menu');
        if (!menu) return;

        if (dropdown.classList.contains('is-active')) {
            menu.style.top = '90%';
        } else {
            menu.style.top = '';
        }
    });
});

document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown.is-active').forEach(d => {
        d.classList.remove('is-active');

        const m = d.querySelector('.dropdown-menu');
        if (m) m.style.top = '';
    });
});
