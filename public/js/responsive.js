// Small-screen helpers shared by every layout (see public/css/responsive.css)
(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        // Wide tables scroll inside their own box instead of stretching the page
        document.querySelectorAll('table').forEach(function (table) {
            var parent = table.parentElement;
            if (!parent || parent.classList.contains('table-responsive') || parent.classList.contains('rs-table-scroll')) return;
            var wrap = document.createElement('div');
            wrap.className = 'rs-table-scroll';
            parent.insertBefore(wrap, table);
            wrap.appendChild(table);
        });

        // Staff layouts: hamburger button that opens the sidebar on phones
        if (!document.querySelector('.rs-staff-sidebar')) return;

        var body = document.body;
        var toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'rs-nav-toggle';
        toggle.setAttribute('aria-label', 'Open menu');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.innerHTML = '&#9776;';

        var backdrop = document.createElement('div');
        backdrop.className = 'rs-nav-backdrop';

        function setOpen(open) {
            body.classList.toggle('rs-nav-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        }

        toggle.addEventListener('click', function () {
            setOpen(!body.classList.contains('rs-nav-open'));
        });
        backdrop.addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setOpen(false);
        });

        body.appendChild(backdrop);
        body.appendChild(toggle);
    });
})();
