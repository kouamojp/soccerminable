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
  const hamburger = document.getElementById('hamburger');
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
  ['impact', 'hero'].forEach(id => {
    const el = document.getElementById(id);
    if (el) numObserver.observe(el);
  });

  /* ── BACK TO TOP ── */
  const backTop = document.getElementById('back-top');
  window.addEventListener('scroll', () => backTop.classList.toggle('visible', window.scrollY > 400), { passive: true });
  backTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

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
      parentName1: document.getElementById('parentName_1'),
      parentName2: document.getElementById('parentName_2'),
      parentAddress: document.getElementById('parentAddress'),
      parentEmail: document.getElementById('parentEmail'),
      parentPhone: document.getElementById('parentPhone'),
      childName: document.getElementById('childName'),
      childDOB: document.getElementById('childDOB'),
      location: document.getElementById('location'),
      program: document.getElementById('program'),
      message: document.getElementById('message'),
      consent1: document.getElementById('consent-1'),
      consent2: document.getElementById('consent-2')
    };

    // Validation
    if (!fields.parentName1.value.trim() || !fields.parentEmail.value.trim() || !fields.program.value) {
      showToast(lang === 'fr' ? '⚠️ Veuillez remplir les champs obligatoires (*).' : '⚠️ Please fill in all required fields (*).');
      return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fields.parentEmail.value)) {
      showToast(lang === 'fr' ? '⚠️ Courriel invalide.' : '⚠️ Invalid email address.');
      fields.parentEmail.focus(); return;
    }
    if (!fields.consent1.checked) {
      showToast(lang === 'fr' ? '⚠️ Vous devez accepter la décharge de responsabilité.' : '⚠️ You must accept the liability waiver.');
      return;
    }

    const btn = document.querySelector('.form-submit');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.textContent = lang === 'fr' ? '⏳ Envoi...' : '⏳ Sending...';

    const data = {
      parentName: fields.parentName1.value,
      parentName2: fields.parentName2.value,
      address: fields.parentAddress.value,
      email: fields.parentEmail.value,
      phone: fields.parentPhone.value,
      childName: fields.childName.value,
      childDOB: fields.childDOB.value,
      location: fields.location.value,
      program: fields.program.value,
      message: fields.message.value,
      consent_liability: fields.consent1.checked,
      consent_media: fields.consent2.checked,
      submitted: new Date().toLocaleString('fr-CA'),
    };

    // --- STRIPE INTEGRATION ---
    try {
      btn.textContent = lang === 'fr' ? '💳 Paiement...' : '💳 Checkout...';
      
      const response = await fetch('checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'Erreur de serveur');
      
      // Redirection vers Stripe Checkout
      window.location.href = result.url;

    } catch (err) {
      console.error(err);
      showToast(lang === 'fr' ? `❌ Erreur : ${err.message}` : `❌ Error: ${err.message}`);
      btn.disabled = false;
      btn.innerHTML = origText;
    }
  };

  /* ── GESTION RETOUR PAIEMENT ── */
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('payment') === 'success') {
    document.getElementById('regFormInner').style.display = 'none';
    document.getElementById('formSuccess').classList.add('show');
    // Scroll au formulaire pour voir le message de succès
    const formSection = document.getElementById('register');
    if (formSection) formSection.scrollIntoView({ behavior: 'smooth' });
  } else if (urlParams.get('payment') === 'cancel') {
    showToast('⚠️ Paiement annulé. Vous pouvez réessayer.', 5000);
  }
});

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
