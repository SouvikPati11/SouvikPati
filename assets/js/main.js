/* souvikpati.in — front-end interactions (vanilla JS, no dependencies) */
(function () {
  'use strict';

  /* ---- Mobile navigation ---- */
  var toggle = document.getElementById('navToggle');
  var menu = document.getElementById('navMenu');
  if (toggle && menu) {
    toggle.addEventListener('click', function () {
      var open = menu.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    });
    menu.addEventListener('click', function (e) {
      if (e.target.tagName === 'A') {
        menu.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ---- Header shadow on scroll ---- */
  var header = document.getElementById('siteHeader');
  if (header) {
    var onScroll = function () {
      header.classList.toggle('scrolled', window.scrollY > 8);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ---- FAQ accordion ---- */
  document.querySelectorAll('.faq-q').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.faq-item');
      var answer = item.querySelector('.faq-a');
      var isOpen = item.classList.toggle('open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      answer.style.maxHeight = isOpen ? answer.scrollHeight + 'px' : '0px';
    });
  });

  /* ---- Reveal on scroll ---- */
  var revealEls = document.querySelectorAll('.reveal');
  if (revealEls.length && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('in'); });
  }

  /* ---- Contact form (progressive enhancement via AJAX) ---- */
  var form = document.getElementById('contactForm');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('[type="submit"]');
      var status = document.getElementById('formStatus');
      var original = btn ? btn.textContent : '';
      if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }
      if (status) { status.textContent = ''; status.className = 'flash'; status.hidden = true; }

      // Clear previous field errors
      form.querySelectorAll('.field-error').forEach(function (el) { el.remove(); });

      fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new FormData(form)
      })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (res) {
          var d = res.data || {};
          if (res.ok && d.ok) {
            form.reset();
            if (status) {
              status.textContent = d.message || 'Thanks — your message has been sent.';
              status.className = 'flash flash-success';
              status.hidden = false;
            }
          } else {
            if (d.errors) {
              Object.keys(d.errors).forEach(function (name) {
                var input = form.querySelector('[name="' + name + '"]');
                if (input) {
                  var err = document.createElement('div');
                  err.className = 'field-error';
                  err.textContent = d.errors[name];
                  input.parentNode.appendChild(err);
                }
              });
            }
            if (status) {
              status.textContent = d.error || 'Please check the form and try again.';
              status.className = 'flash flash-error';
              status.hidden = false;
            }
          }
        })
        .catch(function () {
          if (status) {
            status.textContent = 'Network error. Please try again.';
            status.className = 'flash flash-error';
            status.hidden = false;
          }
        })
        .finally(function () {
          if (btn) { btn.disabled = false; btn.textContent = original; }
        });
    });
  }

  /* ---- Current year in footer (if present) ---- */
  var y = document.getElementById('year');
  if (y) { y.textContent = new Date().getFullYear(); }
})();
