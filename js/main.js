/* ============================================
   SOCCERMIDABLE — Main JavaScript v3
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── LANGUAGE SWITCH ── */
  const setLang = (lang) => {
    document.documentElement.setAttribute('data-lang', lang);
    document.body.setAttribute('data-lang', lang);
    document.querySelectorAll('.lang-btn').forEach(b =>
      b.classList.toggle('active', b.dataset.lang === lang)
    );
    localStorage.setItem('sm-lang', lang);
  };
  setLang(localStorage.getItem('sm-lang') || 'fr');
  window.setLang = setLang;

  /* ── NAVBAR SCROLL ── */
  const navbar = document.getElementById('navbar');
  let lastScroll = 0;
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 60);
    lastScroll = window.scrollY;
  }, { passive: true });

  /* ── MOBILE MENU ── */
  const hamburger  = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobileMenu');
  let menuOpen = false;

  window.toggleMenu = () => {
    menuOpen = !menuOpen;
    mobileMenu.classList.toggle('open', menuOpen);
    hamburger.innerHTML = menuOpen
      ? '<span style="transform:rotate(45deg) translate(5px,5px)"></span><span style="opacity:0;width:0"></span><span style="transform:rotate(-45deg) translate(5px,-5px)"></span>'
      : '<span></span><span></span><span></span>';
    // Prevent body scroll when menu open
    document.body.style.overflow = menuOpen ? 'hidden' : '';
  };
  window.closeMenu = () => {
    menuOpen = false;
    mobileMenu.classList.remove('open');
    hamburger.innerHTML = '<span></span><span></span><span></span>';
    document.body.style.overflow = '';
  };

  /* ── SCROLL REVEAL ── */
  const revealObserver = new IntersectionObserver(
    entries => entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); }),
    { threshold: 0.08, rootMargin: '0px 0px -40px 0px' }
  );
  document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => revealObserver.observe(el));

  /* ── COUNTER ANIMATION ── */
  const animateNum = (el) => {
    const raw = el.textContent.trim();
    const number = parseInt(raw.replace(/[^0-9]/g, ''), 10);
    if (!number || number < 2) return;
    const suffix = raw.replace(/[0-9]/g, '').replace(/\s/g, '');
    let start;
    const step = (ts) => {
      if (!start) start = ts;
      const p = Math.min((ts - start) / 1800, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.floor(eased * number).toLocaleString() + suffix;
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };
  const numObserver = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.querySelectorAll('.kpi-num, .hstat-num').forEach(animateNum);
        numObserver.unobserve(e.target);
      }
    });
  }, { threshold: 0.25 });
  ['impact','hero'].forEach(id => {
    const el = document.getElementById(id);
    if (el) numObserver.observe(el);
  });

  /* ── BACK TO TOP ── */
  const backTop = document.getElementById('back-top');
  window.addEventListener('scroll', () => backTop.classList.toggle('visible', window.scrollY > 400), { passive: true });
  backTop.addEventListener('click', () => window.scrollTo({ top:0, behavior:'smooth' }));

  /* ── TOAST ── */
  window.showToast = (msg) => {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
  };

  /* ── COPY WIDGET CODE ── */
  window.copyWidget = () => {
    const code = document.getElementById('widgetCode').textContent;
    navigator.clipboard.writeText(code).then(() => {
      const btn = document.querySelector('.code-copy');
      const orig = btn.textContent;
      btn.textContent = '✓ Copié!';
      btn.style.background = '#34C759';
      setTimeout(() => { btn.textContent = orig; btn.style.background = ''; }, 2500);
      showToast('✅ Code copié!');
    }).catch(() => showToast('❌ Échec de la copie.'));
  };

  /* ── REGISTRATION FORM ── */
  window.submitForm = async () => {
    const lang = document.body.getAttribute('data-lang') || 'fr';
    const fields = {
      parentFirst : document.getElementById('parentFirst'),
      parentLast  : document.getElementById('parentLast'),
      parentEmail : document.getElementById('parentEmail'),
      parentPhone : document.getElementById('parentPhone'),
      childName   : document.getElementById('childName'),
      childAge    : document.getElementById('childAge'),
      program     : document.getElementById('program'),
      message     : document.getElementById('message'),
    };
    if (!fields.parentFirst.value.trim() || !fields.parentEmail.value.trim()) {
      showToast(lang === 'fr' ? '⚠️ Veuillez remplir les champs obligatoires.' : '⚠️ Please fill in the required fields.');
      fields.parentFirst.focus(); return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fields.parentEmail.value)) {
      showToast(lang === 'fr' ? '⚠️ Courriel invalide.' : '⚠️ Invalid email address.');
      fields.parentEmail.focus(); return;
    }
    const btn = document.querySelector('.form-submit');
    btn.disabled = true;
    btn.textContent = lang === 'fr' ? '⏳ Envoi...' : '⏳ Sending...';

    const FORMSPREE_ID = 'YOUR_FORM_ID';
    const data = {
      parentName : `${fields.parentFirst.value} ${fields.parentLast.value}`,
      email      : fields.parentEmail.value,
      phone      : fields.parentPhone.value,
      child      : fields.childName.value,
      age        : fields.childAge.value,
      program    : fields.program.value,
      message    : fields.message.value,
      submitted  : new Date().toLocaleString('fr-CA'),
    };

    if (FORMSPREE_ID !== 'YOUR_FORM_ID') {
      try {
        const res = await fetch(`https://formspree.io/f/${FORMSPREE_ID}`, {
          method:'POST', headers:{'Accept':'application/json','Content-Type':'application/json'},
          body: JSON.stringify({ _subject:'Inscription SoccerMidable', ...data }),
        });
        if (!res.ok) throw new Error();
      } catch {
        showToast('❌ Erreur réseau.');
        btn.disabled = false;
        btn.textContent = lang === 'fr' ? "⚽ S'inscrire" : '⚽ Register Now';
        return;
      }
    } else {
      const mailBody = encodeURIComponent(
        `Nouvelle inscription SoccerMidable\n\nParent: ${data.parentName}\nCourriel: ${data.email}\nTél: ${data.phone}\nEnfant: ${data.child} (${data.age})\nProgramme: ${data.program}\nMessage: ${data.message}\n\nDate: ${data.submitted}`
      );
      window.open(`mailto:info@soccermidable.com?subject=Inscription%20SoccerMidable&body=${mailBody}`);
    }

    document.getElementById('regFormInner').style.display = 'none';
    document.getElementById('formSuccess').classList.add('show');
    window.scrollTo({ top: document.getElementById('register').offsetTop - 80, behavior:'smooth' });
  };

  /* ── SMOOTH ANCHOR SCROLL ── */
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', (e) => {
      const target = document.querySelector(link.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      window.scrollTo({ top: target.getBoundingClientRect().top + window.pageYOffset - 75, behavior:'smooth' });
    });
  });

  /* ── LAZY LOAD IFRAMES ── */
  const lazyIframes = new IntersectionObserver(
    entries => entries.forEach(e => {
      if (e.isIntersecting) {
        const iframe = e.target;
        if (iframe.dataset.src) {
          iframe.src = iframe.dataset.src;
        }
        lazyIframes.unobserve(iframe);
      }
    }),
    { threshold: 0.1, rootMargin: '200px' }
  );
  document.querySelectorAll('iframe[data-src]').forEach(f => lazyIframes.observe(f));

});
